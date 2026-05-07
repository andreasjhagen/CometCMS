<?php

declare(strict_types=1);

namespace CometCMS\Fields;

use CometCMS\Core\Security;

final class JsonFieldType extends AbstractFieldType
{
    public function validate(mixed $value, array $config, array $context = []): array
    {
        $required = parent::validate($value, $config, $context);

        if (($required['valid'] ?? false) === false || $this->isEmpty($value) || is_array($value)) {
            return $required;
        }

        json_decode((string) $value, true);

        return json_last_error() === JSON_ERROR_NONE ? ['valid' => true] : $this->error('Enter valid JSON.', 'invalid_json');
    }

    public function normalize(mixed $value, array $config, array $context = []): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        if ($this->isEmpty($value)) {
            return null;
        }

        return json_decode((string) $value, true);
    }

    public function renderInput(string $name, mixed $value, array $config, array $context = []): string
    {
        $json = is_array($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : (string) $value;

        return '<textarea name="' . Security::e($name) . '" rows="6" spellcheck="false">' . Security::e($json ?: '{}') . '</textarea>';
    }
}

