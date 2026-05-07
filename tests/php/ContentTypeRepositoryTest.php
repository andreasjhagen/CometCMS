<?php

declare(strict_types=1);

use CometCMS\Content\ContentTypeRepository;

test('content type repository saves normalized schemas', function (): void {
    $repository = new ContentTypeRepository();

    $repository->save([
        'name' => 'Blog Posts',
        'label' => 'Blog Posts',
        'icon' => 'MDI:Post',
        'locales' => ['EN_us', 'de DE', ''],
        'default_locale' => 'de-de',
        'fields' => [
            'body' => ['type' => 'markdown'],
        ],
    ]);

    $schema = $repository->find('blog-posts');

    assert_same('blog-posts', $schema['name']);
    assert_same('mdi:post', $schema['icon']);
    assert_same(['en-us', 'de-de'], $schema['locales']);
    assert_same('de-de', $schema['default_locale']);
    assert_same(['body', 'title', 'slug'], array_keys($schema['fields']));
});

test('content type repository reorders saved schemas', function (): void {
    $repository = new ContentTypeRepository();

    $repository->save(['name' => 'pages']);
    $repository->save(['name' => 'posts']);
    $repository->reorder(['posts', 'pages']);

    assert_same(['posts', 'pages'], array_column($repository->all(), 'name'));
});
