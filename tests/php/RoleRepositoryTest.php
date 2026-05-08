<?php

declare(strict_types=1);

use CometCMS\Auth\RoleRepository;

test('role repository all returns default roles in expected order', function (): void {
    $roles = new RoleRepository();
    $all = $roles->all();

    assert_same(3, count($all));
    assert_same('admin', $all[0]['id'] ?? null);
    assert_same('editor', $all[1]['id'] ?? null);
    assert_same('viewer', $all[2]['id'] ?? null);
});

test('role repository find and exists work by id', function (): void {
    $roles = new RoleRepository();

    assert_same('editor', $roles->find('editor')['id'] ?? null);
    assert_true($roles->exists('viewer'));
    assert_false($roles->exists('missing'));
});

test('role repository permissions fall back to viewer defaults for unknown ids', function (): void {
    $roles = new RoleRepository();
    $fallback = RoleRepository::defaultPermissions('viewer');

    assert_same($fallback, $roles->permissions('missing'));
});

test('role repository seed is idempotent', function (): void {
    $roles = new RoleRepository();
    $roles->seed();

    $filesAfterFirst = glob(COMET_STORAGE . '/roles/*.json') ?: [];

    $roles->seed();
    $filesAfterSecond = glob(COMET_STORAGE . '/roles/*.json') ?: [];

    assert_same(3, count($filesAfterFirst));
    assert_same(3, count($filesAfterSecond));
});

test('role repository create update and delete non-system role', function (): void {
    $roles = new RoleRepository();

    $created = $roles->create([
        'name' => 'Content Manager',
        'permissions' => [
            ['effect' => 'allow', 'actions' => ['content.read'], 'resources' => ['content:*']],
        ],
    ]);

    assert_same('content-manager', $created['id'] ?? null);

    $updated = $roles->update('content-manager', [
        'label' => 'Content Team',
        'permissions' => [
            ['effect' => 'allow', 'actions' => ['content.update'], 'resources' => ['content:posts:*']],
        ],
    ]);

    assert_same('Content Team', $updated['label'] ?? null);
    assert_same('content.update', $updated['permissions'][0]['actions'][0] ?? null);

    $roles->delete('content-manager');
    assert_null($roles->find('content-manager'));
});

test('role repository prevents deleting admin role', function (): void {
    $roles = new RoleRepository();

    assert_throws(InvalidArgumentException::class, static function () use ($roles): void {
        $roles->delete('admin');
    });
});

test('role repository rejects updates to locked roles', function (): void {
    $roles = new RoleRepository();

    assert_throws(InvalidArgumentException::class, static function () use ($roles): void {
        $roles->update('admin', ['label' => 'Super Admin']);
    });
});

test('role repository soft deletes system roles', function (): void {
    $roles = new RoleRepository();

    $roles->delete('viewer');

    $viewerPath = COMET_STORAGE . '/roles/viewer.json';
    assert_file_exists_at($viewerPath);

    $stored = json_decode((string) file_get_contents($viewerPath), true);
    assert_true(is_string($stored['deleted_at'] ?? null));
    assert_null($roles->find('viewer'));

    $ids = array_map(static fn(array $role): string => (string) ($role['id'] ?? ''), $roles->all());
    assert_false(in_array('viewer', $ids, true));
});

test('role repository defaultPermissions falls back for unknown ids', function (): void {
    assert_same(
        RoleRepository::defaultPermissions('viewer'),
        RoleRepository::defaultPermissions('missing')
    );
});
