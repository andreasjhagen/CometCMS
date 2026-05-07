<?php

declare(strict_types=1);

namespace CometCMS\Fields;

use CometCMS\Core\Security;

final class NumberFieldType extends AbstractFieldType
{
    public function validate(mixed $value, array $config, array $context = []): array
    {
        $required = parent::validate($value, $config, $context);

        if (($required['valid'] ?? false) === false || $this->isEmpty($value)) {
            return $required;
        }

        return is_numeric($value) ? ['valid' => true] : $this->error('Enter a valid number.', 'invalid_number');
    }

    public function normalize(mixed $value, array $config, array $context = []): mixed
    {
        if ($this->isEmpty($value)) {
            return null;
        }

        return str_contains((string) $value, '.') ? (float) $value : (int) $value;
    }

    public function renderInput(string $name, mixed $value, array $config, array $context = []): string
    {
        return '<input type="number" step="any" name="' . Security::e($name) . '" value="' . Security::e((string) $value) . '">';
    }
}

