<?php

declare(strict_types=1);

namespace CometCMS\Fields;

use CometCMS\Core\Security;

abstract class AbstractFieldType implements FieldType
{
    public function validate(mixed $value, array $config, array $context = []): array
    {
        if (($config['required'] ?? false) && $this->isEmpty($value)) {
            return ['valid' => false, 'message' => 'This field is required.', 'code' => 'required'];
        }

        return ['valid' => true];
    }

    public function normalize(mixed $value, array $config, array $context = []): mixed
    {
        return is_string($value) ? trim($value) : $value;
    }

    public function renderInput(string $name, mixed $value, array $config, array $context = []): string
    {
        return '<input name="' . Security::e($name) . '" value="' . Security::e((string) $value) . '">';
    }

    protected function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    protected function error(string $message, string $code): array
    {
        return ['valid' => false, 'message' => $message, 'code' => $code];
    }
}

