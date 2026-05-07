<?php

declare(strict_types=1);

namespace CometCMS\Fields;

use CometCMS\Core\Security;

class DateFieldType extends AbstractFieldType
{
    public function validate(mixed $value, array $config, array $context = []): array
    {
        $required = parent::validate($value, $config, $context);

        if (($required['valid'] ?? false) === false || $this->isEmpty($value)) {
            return $required;
        }

        return strtotime((string) $value) !== false ? ['valid' => true] : $this->error('Enter a valid date.', 'invalid_date');
    }

    public function renderInput(string $name, mixed $value, array $config, array $context = []): string
    {
        return '<input type="date" name="' . Security::e($name) . '" value="' . Security::e(substr((string) $value, 0, 10)) . '">';
    }
}

