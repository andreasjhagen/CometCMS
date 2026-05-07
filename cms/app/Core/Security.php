<?php

declare(strict_types=1);

namespace CometCMS\Core;

final class Security
{
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function csrfToken(): string
    {
        $key = (string) comet_config('security.csrf_key', 'cometcms_csrf');

        if (empty($_SESSION[$key]) || !is_string($_SESSION[$key])) {
            $_SESSION[$key] = bin2hex(random_bytes(32));
        }

        return $_SESSION[$key];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::e(self::csrfToken()) . '">';
    }

    public static function verifyCsrf(?string $token): bool
    {
        $key = (string) comet_config('security.csrf_key', 'cometcms_csrf');
        $known = $_SESSION[$key] ?? '';

        return is_string($token) && is_string($known) && hash_equals($known, $token);
    }

    public static function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_-]+/', '-', $value) ?? '';
        $value = trim($value, '-_');

        return $value !== '' ? $value : bin2hex(random_bytes(4));
    }

    public static function assertSafeName(string $value): void
    {
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
            throw new \InvalidArgumentException('Only letters, numbers, dashes, and underscores are allowed.');
        }
    }

    public static function now(): string
    {
        return gmdate('Y-m-d\TH:i:s\Z');
    }

    public static function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    public static function opaqueId(int $bytes = 8): string
    {
        return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
    }

    public static function isUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value);
    }
}
