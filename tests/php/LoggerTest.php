<?php

declare(strict_types=1);

use CometCMS\Logging\Logger;
use CometCMS\Workspaces\WorkspaceContext;

function comet_logger_test_read_lines(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    return is_array($lines) ? $lines : [];
}

test('logger info warning and error write JSON lines', function (): void {
    $logPath = COMET_STORAGE . '/logs/logger-test.log';
    $logger = new Logger($logPath);

    $logger->info('first');
    $logger->warning('second');
    $logger->error('third');

    assert_file_exists_at($logPath);
    $lines = comet_logger_test_read_lines($logPath);
    assert_same(3, count($lines));

    $entries = array_map(static fn(string $line): array => json_decode($line, true) ?: [], $lines);
    assert_same('info', $entries[0]['level'] ?? null);
    assert_same('warning', $entries[1]['level'] ?? null);
    assert_same('error', $entries[2]['level'] ?? null);
});

test('logger strips sensitive context keys and injects workspace', function (): void {
    WorkspaceContext::setActive('default');

    $logPath = COMET_STORAGE . '/logs/logger-sensitive.log';
    $logger = new Logger($logPath);
    $logger->info('auth', [
        'password' => 'secret',
        'token' => 'abc',
        'api_token' => 'ctcms_x',
        'secret' => 'key',
        'user_id' => 'admin',
    ]);

    $entry = json_decode((string) file_get_contents($logPath), true);
    $context = $entry['context'] ?? [];

    assert_same('default', $context['workspace'] ?? null);
    assert_same('admin', $context['user_id'] ?? null);
    assert_false(array_key_exists('password', $context));
    assert_false(array_key_exists('token', $context));
    assert_false(array_key_exists('api_token', $context));
    assert_false(array_key_exists('secret', $context));
});

test('logger rotates files larger than 1MB before writing new entries', function (): void {
    $logPath = COMET_STORAGE . '/logs/logger-rotate.log';
    file_put_contents($logPath, str_repeat('x', 1024 * 1024 + 100));

    $logger = new Logger($logPath);
    $logger->info('after-rotation');

    $rotated = glob($logPath . '.*') ?: [];
    assert_true(count($rotated) >= 1);

    $current = comet_logger_test_read_lines($logPath);
    assert_same(1, count($current));
    assert_true(str_contains($current[0], 'after-rotation'));
});
