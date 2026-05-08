<?php

declare(strict_types=1);

use CometCMS\Storage\SettingsStore;

test('settings store all returns bootstrap defaults before explicit save', function (): void {
    $settings = new SettingsStore();
    $all = $settings->all();

    assert_same('default', $all['default_workspace'] ?? null);
    assert_true(is_array($all['workspaces'] ?? null));
});

test('settings store save and all roundtrip', function (): void {
    $settings = new SettingsStore();
    $payload = [
        'site' => ['name' => 'CometCMS'],
        'features' => ['api' => true],
    ];

    $settings->save($payload);

    assert_same($payload, $settings->all());
});

test('settings store uses atomic temp-file writes', function (): void {
    $settings = new SettingsStore();
    $settings->save(['k' => 'v']);

    $tmpFiles = glob(COMET_STORAGE . '/settings.json.*.tmp') ?: [];
    assert_same(0, count($tmpFiles));
    assert_file_exists_at(COMET_STORAGE . '/settings.json');
});
