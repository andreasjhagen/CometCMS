<?php

declare(strict_types=1);

namespace CometCMS\Media;

final class MediaCategory
{
    public static function normalize(string $category): string
    {
        $category = str_replace('\\', '/', $category);
        $category = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $category) ?? '';
        $parts = array_filter(array_map(
            function (string $part): string {
                $part = preg_replace('/\s+/u', ' ', trim($part)) ?? '';

                return substr($part, 0, 80);
            },
            explode('/', $category)
        ), static fn(string $part): bool => $part !== '');

        return substr(implode(' / ', $parts), 0, 240);
    }

    public static function pathsFor(string $category): array
    {
        $category = self::normalize($category);
        if ($category === '') {
            return [];
        }

        $paths = [];
        $current = [];

        foreach (explode(' / ', $category) as $part) {
            $current[] = $part;
            $paths[] = implode(' / ', $current);
        }

        return $paths;
    }

    public static function sort(array $categories): array
    {
        $normalized = array_values(array_unique(array_filter(
            array_map(static fn(mixed $category): string => self::normalize((string) $category), $categories),
            static fn(string $category): bool => $category !== ''
        )));

        usort($normalized, static function (string $a, string $b): int {
            return strcasecmp(str_replace(' / ', "\0", $a), str_replace(' / ', "\0", $b));
        });

        return $normalized;
    }

    public static function matches(string $category, string $categoryPath): bool
    {
        $category = self::normalize($category);
        $categoryPath = self::normalize($categoryPath);

        if ($categoryPath === '') {
            return $category === '';
        }

        return $category === $categoryPath || str_starts_with($category, $categoryPath . ' / ');
    }

    public static function renamePath(string $category, string $oldName, string $newName): string
    {
        if ($category === $oldName) {
            return $newName;
        }

        if (str_starts_with($category, $oldName . ' / ')) {
            return $newName . substr($category, strlen($oldName));
        }

        return $category;
    }
}
