<?php

declare(strict_types=1);

use CometCMS\Storage\JsonStore;

test('json store writes and reads nested documents', function (): void {
    $store = new JsonStore(COMET_STORAGE . '/content');

    $store->write(['id' => 'welcome', 'title' => 'Welcome'], 'pages', 'welcome');

    assert_same(['id' => 'welcome', 'title' => 'Welcome'], $store->read('pages', 'welcome'));
    assert_file_exists_at(COMET_STORAGE . '/content/pages/welcome.json');
});

test('json store blocks unsafe path segments', function (): void {
    $store = new JsonStore(COMET_STORAGE . '/content');

    assert_throws(InvalidArgumentException::class, static fn() => $store->read('pages/../../users'));
});

test('json store returns newest documents first', function (): void {
    $store = new JsonStore(COMET_STORAGE . '/content');

    $store->write(['id' => 'old', 'updated_at' => '2024-01-01T00:00:00Z'], 'pages', 'old');
    $store->write(['id' => 'new', 'updated_at' => '2024-02-01T00:00:00Z'], 'pages', 'new');

    assert_same(['new', 'old'], array_column($store->all('pages'), 'id'));
});
