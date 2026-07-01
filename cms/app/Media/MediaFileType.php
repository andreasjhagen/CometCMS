<?php

declare(strict_types=1);

namespace CometCMS\Media;

final class MediaFileType
{
    public static function matches(string $file, string $type): bool
    {
        if ($type === '' || $type === 'all') {
            return true;
        }

        $ext = strtolower((string) pathinfo($file, PATHINFO_EXTENSION));

        return match ($type) {
            'images' => in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif'], true),
            'video' => in_array($ext, ['mp4', 'webm', 'mov', 'm4v', 'avi', 'mkv', 'mpeg', 'mpg', 'ogv', '3gp', '3g2'], true),
            'audio' => in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac'], true),
            'documents' => in_array($ext, ['pdf', 'doc', 'docx', 'odt', 'xls', 'xlsx', 'ods', 'csv', 'ppt', 'pptx', 'odp', 'txt', 'md', 'rtf'], true),
            'archives' => in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'], true),
            'other' => !self::matches($file, 'images')
                && !self::matches($file, 'video')
                && !self::matches($file, 'audio')
                && !self::matches($file, 'documents')
                && !self::matches($file, 'archives'),
            default => true,
        };
    }
}
