<?php

declare(strict_types=1);

use CometCMS\Auth\PermissionService;

function comet_permission_test_service(): PermissionService
{
    return new PermissionService();
}

function comet_permission_test_token(array $permissions): array
{
    return [
        '_principal_type' => 'token',
        'id' => 'token-user',
        'permissions' => $permissions,
    ];
}

test('permission service lets explicit deny override broader allows', function (): void {
    $service = comet_permission_test_service();
    $principal = comet_permission_test_token([
        ['effect' => 'allow', 'actions' => ['content.*'], 'resources' => ['content:*']],
        ['effect' => 'deny', 'actions' => ['content.delete'], 'resources' => ['content:posts:locked']],
    ]);

    assert_true($service->allows($principal, 'content.update', ['type' => 'content', 'collection' => 'posts', 'id' => 'locked']));
    assert_false($service->allows($principal, 'content.delete', ['type' => 'content', 'collection' => 'posts', 'id' => 'locked']));
    assert_true($service->allows($principal, 'content.delete', ['type' => 'content', 'collection' => 'posts', 'id' => 'open']));
});

test('permission service matches wildcard resources and content slugs', function (): void {
    $service = comet_permission_test_service();
    $principal = comet_permission_test_token([
        ['effect' => 'allow', 'actions' => ['content.read'], 'resources' => ['content:pages:welcome']],
        ['effect' => 'allow', 'actions' => ['media.update'], 'resources' => ['media:category:Docs']],
    ]);

    assert_true($service->allows($principal, 'content.read', [
        'type' => 'content',
        'collection' => 'pages',
        'entry' => ['id' => 'entry-1', 'slug' => 'welcome'],
    ]));
    assert_true($service->allows($principal, 'media.update', [
        'type' => 'media',
        'file' => 'guide.pdf',
        'category' => 'Docs',
    ]));
    assert_false($service->allows($principal, 'media.update', [
        'type' => 'media',
        'file' => 'guide.pdf',
        'category' => 'Images',
    ]));
});

test('permission service restricts changed fields', function (): void {
    $service = comet_permission_test_service();
    $principal = comet_permission_test_token([
        ['effect' => 'allow', 'actions' => ['content.update'], 'resources' => ['content:posts:*'], 'fields' => ['title', 'summary']],
    ]);

    assert_true($service->allows($principal, 'content.update', [
        'type' => 'content',
        'collection' => 'posts',
        'fields' => ['title'],
    ]));
    assert_false($service->allows($principal, 'content.update', [
        'type' => 'content',
        'collection' => 'posts',
        'fields' => ['title', 'secret_notes'],
    ]));
});

test('permission service applies owner status and locale conditions', function (): void {
    $service = comet_permission_test_service();
    $principal = comet_permission_test_token([
        [
            'effect' => 'allow',
            'actions' => ['content.update'],
            'resources' => ['content:posts:*'],
            'conditions' => ['own' => true, 'status' => ['draft'], 'locales' => ['en']],
        ],
    ]);

    $context = [
        'type' => 'content',
        'collection' => 'posts',
        'entry' => ['id' => 'draft-post', 'author_id' => 'token-user', 'status' => 'draft'],
        'principal' => ['id' => 'token-user'],
        'locale' => 'en',
    ];

    assert_true($service->allows($principal, 'content.update', $context));
    assert_false($service->allows($principal, 'content.update', [
        ...$context,
        'entry' => ['id' => 'draft-post', 'author_id' => 'someone-else', 'status' => 'draft'],
    ]));
    assert_false($service->allows($principal, 'content.update', [
        ...$context,
        'entry' => ['id' => 'published-post', 'author_id' => 'token-user', 'status' => 'published'],
    ]));
    assert_false($service->allows($principal, 'content.update', [
        ...$context,
        'locale' => 'de',
    ]));
});
