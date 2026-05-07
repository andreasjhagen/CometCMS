<?php

declare(strict_types=1);

namespace CometCMS\Core;

final class MimeDetector
{
    private const EXTENSION_MAP = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'avif' => 'image/avif',
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
        'mov'  => 'video/quicktime',
        'm4v'  => 'video/x-m4v',
        'avi'  => 'video/x-msvideo',
        'mkv'  => 'video/x-matroska',
        'mpeg' => 'video/mpeg',
        'mpg'  => 'video/mpeg',
        'ogv'  => 'video/ogg',
        'pdf'  => 'application/pdf',
        'txt'  => 'text/plain',
        'csv'  => 'text/csv',
        'md'   => 'text/markdown',
        'rtf'  => 'application/rtf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'odt'  => 'application/vnd.oasis.opendocument.text',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'odp'  => 'application/vnd.oasis.opendocument.presentation',
        'zip'  => 'application/zip',
        '7z'   => 'application/x-7z-compressed',
        'rar'  => 'application/x-rar-compressed',
        'gz'   => 'application/gzip',
        'tar'  => 'application/x-tar',
        'mp3'  => 'audio/mpeg',
        'wav'  => 'audio/wav',
        'ogg'  => 'audio/ogg',
        'm4a'  => 'audio/x-m4a',
        'aac'  => 'audio/aac',
        'flac' => 'audio/flac',
    ];

    public static function detect(string $path, ?string $originalName = null): string
    {
        if (class_exists(\finfo::class)) {
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);

            if (is_string($mime) && $mime !== '') {
                return $mime;
            }
        }

        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($path);

            if (is_string($mime) && $mime !== '') {
                return $mime;
            }
        }

        $extension = strtolower(pathinfo($originalName ?: $path, PATHINFO_EXTENSION));

        return self::EXTENSION_MAP[$extension] ?? 'application/octet-stream';
    }
}
