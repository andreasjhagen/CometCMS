# CometCMS Migrator for WordPress

This WordPress plugin migrates public WordPress post types into CometCMS through the CometCMS public REST API.

## Features

- Admin screen under **Tools > CometCMS Migrator**
- API key based connection to a CometCMS workspace. Use the CometCMS site root URL, for example `https://cms.example.com`, not `/admin/dashboard`.
- Batch migration for posts, pages, and other public post types
- Optional creation of missing CometCMS content types
- Featured image and attached media upload
- Repeatable updates using stored CometCMS entry IDs in WordPress post meta

## Required CometCMS Token Grants

Create an API token in CometCMS with grants for the target workspace:

- `schema.create` on `schema:*` if the plugin should create content types
- `content.create`, `content.update`, and `content.publish` on `content:*`
- `content.read` on `content:*` for update detection
- `media.upload` and `media.update` on `media:*` when migrating media

## Default Schema

For each enabled WordPress post type, the plugin creates a CometCMS content type with:

- `title`
- `slug`
- `wordpress_id`
- `wordpress_type`
- `excerpt`
- `content`
- `featured_image`
- `attachments`
- `original_url`

WordPress content is stored as HTML in the `content` textarea field.
