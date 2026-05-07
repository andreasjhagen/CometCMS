<?php

declare(strict_types=1);

namespace CometCMS\Fields;

use CometCMS\Core\Security;

class TextareaFieldType extends AbstractFieldType
{
    public function renderInput(string $name, mixed $value, array $config, array $context = []): string
    {
        return '<textarea name="' . Security::e($name) . '" rows="4">' . Security::e((string) $value) . '</textarea>';
    }
}
