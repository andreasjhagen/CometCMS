<?php

declare(strict_types=1);

namespace CometCMS\Fields;

use CometCMS\Core\Security;

final class DateTimeFieldType extends DateFieldType
{
    public function renderInput(string $name, mixed $value, array $config, array $context = []): string
    {
        $value = (string) $value;
        $local = $value !== '' ? date('Y-m-d\TH:i', strtotime($value) ?: time()) : '';

        return '<input type="datetime-local" name="' . Security::e($name) . '" value="' . Security::e($local) . '">';
    }
}

