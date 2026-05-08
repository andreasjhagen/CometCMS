<?php

declare(strict_types=1);

use CometCMS\Auth\LoginThrottle;

function comet_login_throttle_test_record_path(string $username, string $ip): string
{
    $normalized = strtolower(trim($username));
    $key = hash('sha256', $normalized . '|' . $ip);

    return COMET_STORAGE . '/cache/login-throttle/' . $key . '.json';
}

test('login throttle status is not limited initially', function (): void {
    $throttle = new LoginThrottle();
    $status = $throttle->status('admin', '127.0.0.1');

    assert_false($status['limited'] ?? true);
    assert_same(0, $status['retry_after'] ?? -1);
});

test('login throttle recordFailure increments and locks out after max attempts', function (): void {
    $throttle = new LoginThrottle();
    $username = 'admin';
    $ip = '127.0.0.1';

    for ($i = 1; $i <= 4; $i++) {
        $status = $throttle->recordFailure($username, $ip);
        assert_false($status['limited'] ?? true);
    }

    $status = $throttle->recordFailure($username, $ip);
    assert_true($status['limited'] ?? false);
    assert_true(($status['retry_after'] ?? 0) > 0);

    $recordPath = comet_login_throttle_test_record_path($username, $ip);
    assert_file_exists_at($recordPath);

    $record = json_decode((string) file_get_contents($recordPath), true);
    assert_same(5, (int) ($record['attempts'] ?? 0));
});

test('login throttle clear removes lockout state', function (): void {
    $throttle = new LoginThrottle();
    $username = 'admin';
    $ip = '127.0.0.1';

    for ($i = 0; $i < 5; $i++) {
        $throttle->recordFailure($username, $ip);
    }

    assert_true($throttle->status($username, $ip)['limited'] ?? false);
    $throttle->clear($username, $ip);

    $status = $throttle->status($username, $ip);
    assert_false($status['limited'] ?? true);
    assert_same(0, $status['retry_after'] ?? -1);
});

test('login throttle resets stale counters after window expiry', function (): void {
    $throttle = new LoginThrottle();
    $username = 'admin';
    $ip = '127.0.0.1';
    $recordPath = comet_login_throttle_test_record_path($username, $ip);

    $staleRecord = [
        'id' => hash('sha256', strtolower(trim($username)) . '|' . $ip),
        'username' => strtolower(trim($username)),
        'ip_hash' => hash('sha256', $ip),
        'attempts' => 4,
        'first_attempt_at' => time() - 301,
        'last_attempt_at' => time() - 301,
        'locked_until' => 0,
        'updated_at' => date(DATE_ATOM),
    ];

    file_put_contents($recordPath, json_encode($staleRecord, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

    $status = $throttle->recordFailure($username, $ip);
    assert_false($status['limited'] ?? true);

    $record = json_decode((string) file_get_contents($recordPath), true);
    assert_same(1, (int) ($record['attempts'] ?? 0));
});

test('login throttle normalizes username when creating lockout key', function (): void {
    $throttle = new LoginThrottle();
    $ip = '127.0.0.1';

    $throttle->recordFailure('  ADMIN ', $ip);
    $throttle->recordFailure('admin', $ip);

    $recordPath = comet_login_throttle_test_record_path('admin', $ip);
    $record = json_decode((string) file_get_contents($recordPath), true);

    assert_same('admin', $record['username'] ?? null);
    assert_same(2, (int) ($record['attempts'] ?? 0));
});
