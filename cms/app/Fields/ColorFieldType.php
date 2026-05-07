<?php

declare(strict_types=1);

namespace CometCMS\Fields;

class ColorFieldType extends AbstractFieldType
{
    public function validate(mixed $value, array $config, array $context = []): array
    {
        $parent = parent::validate($value, $config, $context);
        if (!$parent['valid']) {
            return $parent;
        }

        if ($this->isEmpty($value)) {
            return ['valid' => true];
        }

        if (!preg_match('/^#[0-9a-fA-F]{3}$|^#[0-9a-fA-F]{6}$/', (string) $value)) {
            return $this->error('Must be a valid hex color (e.g. #ff0000).', 'invalid_color');
        }

        return ['valid' => true];
    }

    public function normalize(mixed $value, array $config, array $context = []): mixed
    {
        $trimmed = trim((string) ($value ?? ''));
        return $trimmed === '' ? '' : strtolower($trimmed);
    }
}
