<?php

declare(strict_types=1);

namespace CometCMS\Auth;

use CometCMS\Core\Security;
use CometCMS\Storage\JsonStore;

final class LoginThrottle
{
    private JsonStore $store;
    private int $maxAttempts;
    private int $windowSeconds;
    private int $lockoutSeconds;

    public function __construct()
    {
        $this->store = new JsonStore(COMET_STORAGE . '/cache/login-throttle');
        $this->maxAttempts = max(1, (int) comet_config('security.login_throttle.max_attempts', 5));
        $this->windowSeconds = max(60, (int) comet_config('security.login_throttle.window_seconds', 300));
        $this->lockoutSeconds = max(60, (int) comet_config('security.login_throttle.lockout_seconds', 900));
    }

    public function status(string $username, string $ip): array
    {
        $record = $this->record($username, $ip);
        $now = time();
        $lockedUntil = (int) ($record['locked_until'] ?? 0);

        if ($lockedUntil > $now) {
            return [
                'limited' => true,
                'retry_after' => $lockedUntil - $now,
            ];
        }

        return [
            'limited' => false,
            'retry_after' => 0,
        ];
    }

    public function recordFailure(string $username, string $ip): array
    {
        $record = $this->record($username, $ip);
        $now = time();
        $firstAttemptAt = (int) ($record['first_attempt_at'] ?? 0);
        $attempts = (int) ($record['attempts'] ?? 0);

        if ($firstAttemptAt <= 0 || ($now - $firstAttemptAt) > $this->windowSeconds) {
            $firstAttemptAt = $now;
            $attempts = 0;
        }

        $attempts++;
        $lockedUntil = 0;

        if ($attempts >= $this->maxAttempts) {
            $lockedUntil = $now + $this->lockoutSeconds;
        }

        $this->store->write([
            'id' => $this->key($username, $ip),
            'username' => $this->normalizeUsername($username),
            'ip_hash' => hash('sha256', $ip),
            'attempts' => $attempts,
            'first_attempt_at' => $firstAttemptAt,
            'last_attempt_at' => $now,
            'locked_until' => $lockedUntil,
            'updated_at' => Security::now(),
        ], $this->key($username, $ip));

        return [
            'limited' => $lockedUntil > $now,
            'retry_after' => max(0, $lockedUntil - $now),
        ];
    }

    public function clear(string $username, string $ip): void
    {
        $this->store->delete($this->key($username, $ip));
    }

    private function record(string $username, string $ip): array
    {
        return $this->store->read($this->key($username, $ip)) ?? [];
    }

    private function key(string $username, string $ip): string
    {
        return hash('sha256', $this->normalizeUsername($username) . '|' . $ip);
    }

    private function normalizeUsername(string $username): string
    {
        return strtolower(trim($username));
    }
}
