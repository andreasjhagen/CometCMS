<?php

declare(strict_types=1);

namespace CometCMS\Storage;

final class SettingsStore
{
    private string $file;

    public function __construct()
    {
        $this->file = COMET_STORAGE . '/settings.json';
    }

    public function all(): array
    {
        if (!is_file($this->file)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($this->file), true);

        return is_array($data) ? $data : [];
    }

    public function save(array $data): void
    {
        $tmp = $this->file . '.' . bin2hex(random_bytes(6)) . '.tmp';
        file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL, LOCK_EX);
        rename($tmp, $this->file);
    }
}
