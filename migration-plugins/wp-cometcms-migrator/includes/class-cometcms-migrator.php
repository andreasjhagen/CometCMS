<?php

if (!defined('ABSPATH')) {
    exit;
}

final class CometCMS_Migrator
{
    private CometCMS_Migrator_Api_Client $client;
    private array $settings;
    private array $media_cache = [];

    public function __construct(array $settings)
    {
        $this->settings = $settings;
        $this->client = new CometCMS_Migrator_Api_Client($settings);
    }

    public static function default_settings(): array
    {
        return [
            'base_url' => '',
            'workspace' => 'default',
            'api_key' => '',
            'timeout' => 60,
            'batch_size' => 10,
            'post_types' => ['post', 'page'],
            'collections' => [
                'post' => 'wordpress-posts',
                'page' => 'wordpress-pages',
            ],
            'create_schema' => true,
            'update_existing' => true,
            'migrate_media' => true,
            'media_category' => 'WordPress Migration',
        ];
    }

    public static function sanitize_settings(array $input): array
    {
        $defaults = self::default_settings();
        $post_types = array_values(array_filter(array_map('sanitize_key', (array) ($input['post_types'] ?? []))));
        $collections = [];

        foreach ($post_types as $post_type) {
            $collections[$post_type] = sanitize_title((string) ($input['collections'][$post_type] ?? $defaults['collections'][$post_type] ?? 'wordpress-' . $post_type));
        }

        return [
            'base_url' => esc_url_raw(CometCMS_Migrator_Api_Client::normalize_base_url((string) ($input['base_url'] ?? ''))),
            'workspace' => sanitize_title((string) ($input['workspace'] ?? $defaults['workspace'])),
            'api_key' => sanitize_text_field((string) ($input['api_key'] ?? '')),
            'timeout' => max(10, min(300, (int) ($input['timeout'] ?? $defaults['timeout']))),
            'batch_size' => max(1, min(50, (int) ($input['batch_size'] ?? $defaults['batch_size']))),
            'post_types' => $post_types ?: $defaults['post_types'],
            'collections' => $collections ?: $defaults['collections'],
            'create_schema' => !empty($input['create_schema']),
            'update_existing' => !empty($input['update_existing']),
            'migrate_media' => !empty($input['migrate_media']),
            'media_category' => sanitize_text_field((string) ($input['media_category'] ?? $defaults['media_category'])),
        ];
    }

    public function test_connection(): array|WP_Error
    {
        return $this->client->health();
    }

    public function counts(): array
    {
        $counts = [];

        foreach ((array) ($this->settings['post_types'] ?? []) as $post_type) {
            $post_type = sanitize_key($post_type);
            $counts[$post_type] = (int) wp_count_posts($post_type)->publish
                + (int) wp_count_posts($post_type)->draft
                + (int) wp_count_posts($post_type)->private
                + (int) wp_count_posts($post_type)->future;
        }

        return $counts;
    }

    public function migrate_batch(string $post_type, int $offset, int $limit): array|WP_Error
    {
        $post_type = sanitize_key($post_type);
        $collection = $this->collection_for($post_type);

        if ($collection === '') {
            return new WP_Error('cometcms_collection_missing', __('No CometCMS collection is configured for this post type.', 'cometcms-migrator'));
        }

        if (!empty($this->settings['create_schema'])) {
            $schema_result = $this->ensure_schema($post_type, $collection);
            if (is_wp_error($schema_result)) {
                return $schema_result;
            }
        }

        $query = new WP_Query([
            'post_type' => $post_type,
            'post_status' => ['publish', 'draft', 'private', 'future'],
            'posts_per_page' => $limit,
            'offset' => $offset,
            'orderby' => 'ID',
            'order' => 'ASC',
            'no_found_rows' => false,
        ]);

        $result = [
            'post_type' => $post_type,
            'collection' => $collection,
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'total' => (int) $query->found_posts,
            'next_offset' => $offset + count($query->posts),
            'done' => ($offset + count($query->posts)) >= (int) $query->found_posts,
            'messages' => [],
        ];

        foreach ($query->posts as $post) {
            $migration = $this->migrate_post($post, $collection);
            $result['processed']++;

            if (is_wp_error($migration)) {
                $result['failed']++;
                $result['messages'][] = sprintf('#%d %s: %s', (int) $post->ID, $post->post_title, $migration->get_error_message());
                continue;
            }

            $result[$migration['action']]++;
            $result['messages'][] = sprintf('#%d %s: %s', (int) $post->ID, $post->post_title, $migration['action']);
        }

        wp_reset_postdata();

        return $result;
    }

    private function ensure_schema(string $post_type, string $collection): bool|WP_Error
    {
        $existing = $this->client->get_content_type($collection);
        if (!is_wp_error($existing)) {
            return true;
        }

        $error_data = $existing->get_error_data();
        if ((int) ($error_data['status'] ?? 0) !== 404) {
            return $existing;
        }

        $label = get_post_type_object($post_type)->labels->name ?? ucfirst($post_type);
        $schema = [
            'name' => $collection,
            'label' => 'WordPress ' . $label,
            'icon' => $post_type === 'page' ? 'mdi:file-document-outline' : 'mdi:post-outline',
            'singleton' => false,
            'slug_field' => 'slug',
            'slug_source' => 'title',
            'fields' => [
                'title' => ['type' => 'text', 'required' => true],
                'slug' => ['type' => 'slug', 'required' => true, 'unique' => true],
                'wordpress_id' => ['type' => 'number', 'required' => false],
                'wordpress_type' => ['type' => 'text', 'required' => false],
                'excerpt' => ['type' => 'textarea', 'required' => false],
                'content' => ['type' => 'textarea', 'required' => false],
                'featured_image' => ['type' => 'media', 'multiple' => false],
                'attachments' => ['type' => 'media', 'multiple' => true],
                'original_url' => ['type' => 'text', 'required' => false],
            ],
        ];

        $created = $this->client->create_content_type($schema);

        return is_wp_error($created) ? $created : true;
    }

    private function migrate_post(WP_Post $post, string $collection): array|WP_Error
    {
        $payload = $this->post_payload($post);
        $identifier = (string) $payload['slug'];
        $existing_id = (string) get_post_meta($post->ID, $this->meta_key($collection), true);
        $action = 'created';

        if (!empty($this->settings['update_existing'])) {
            $lookup = $existing_id !== '' ? $this->client->get_entry($collection, $existing_id) : $this->client->get_entry($collection, $identifier);
            if (!is_wp_error($lookup) && !empty($lookup['data']['id'])) {
                $updated = $this->client->update_entry($collection, (string) $lookup['data']['id'], $payload);
                if (is_wp_error($updated)) {
                    return $updated;
                }

                update_post_meta($post->ID, $this->meta_key($collection), (string) ($updated['data']['id'] ?? ''));
                return ['action' => 'updated', 'entry' => $updated['data'] ?? []];
            }
        }

        $created = $this->client->create_entry($collection, $payload);
        if (is_wp_error($created)) {
            return $created;
        }

        update_post_meta($post->ID, $this->meta_key($collection), (string) ($created['data']['id'] ?? ''));

        return ['action' => $action, 'entry' => $created['data'] ?? []];
    }

    private function post_payload(WP_Post $post): array
    {
        $featured = [];
        $attachments = [];

        if (!empty($this->settings['migrate_media'])) {
            $featured_id = get_post_thumbnail_id($post);
            if ($featured_id) {
                $filename = $this->upload_attachment((int) $featured_id);
                if ($filename !== '') {
                    $featured[] = $filename;
                }
            }

            foreach (get_attached_media('', $post->ID) as $attachment) {
                $filename = $this->upload_attachment((int) $attachment->ID);
                if ($filename !== '' && !in_array($filename, $featured, true)) {
                    $attachments[] = $filename;
                }
            }
        }

        return [
            'title' => html_entity_decode(get_the_title($post), ENT_QUOTES, get_bloginfo('charset')),
            'slug' => $post->post_name ?: sanitize_title($post->post_title),
            'status' => $this->map_status($post->post_status),
            'published_at' => $post->post_date_gmt !== '0000-00-00 00:00:00' ? gmdate('c', strtotime($post->post_date_gmt)) : null,
            'wordpress_id' => (int) $post->ID,
            'wordpress_type' => (string) $post->post_type,
            'excerpt' => wp_strip_all_tags((string) ($post->post_excerpt ?: wp_trim_words($post->post_content, 55, ''))),
            'content' => apply_filters('the_content', $post->post_content),
            'featured_image' => $featured,
            'attachments' => array_values(array_unique($attachments)),
            'original_url' => get_permalink($post),
        ];
    }

    private function upload_attachment(int $attachment_id): string
    {
        if (isset($this->media_cache[$attachment_id])) {
            return $this->media_cache[$attachment_id];
        }

        $path = get_attached_file($attachment_id);
        if (!is_string($path) || $path === '' || !is_readable($path)) {
            $this->media_cache[$attachment_id] = '';
            return '';
        }

        $filename = basename($path);
        $uploaded = $this->client->upload_media($path, $filename, (string) ($this->settings['media_category'] ?? ''));
        if (is_wp_error($uploaded) || empty($uploaded['data'][0]['filename'])) {
            $this->media_cache[$attachment_id] = '';
            return '';
        }

        $stored = (string) $uploaded['data'][0]['filename'];
        $alt = (string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        $title = (string) get_the_title($attachment_id);

        if ($alt !== '' || $title !== '') {
            $this->client->update_media_meta($stored, $alt, $title);
        }

        $this->media_cache[$attachment_id] = $stored;

        return $stored;
    }

    private function collection_for(string $post_type): string
    {
        return sanitize_title((string) ($this->settings['collections'][$post_type] ?? ''));
    }

    private function map_status(string $status): string
    {
        return match ($status) {
            'publish' => 'published',
            'private' => 'protected',
            default => 'draft',
        };
    }

    private function meta_key(string $collection): string
    {
        return '_cometcms_migration_' . md5((string) ($this->settings['workspace'] ?? '') . '|' . $collection);
    }
}
