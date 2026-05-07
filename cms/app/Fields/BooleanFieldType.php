<?php

declare(strict_types=1);

namespace CometCMS\Fields;

use CometCMS\Core\Security;

final class BooleanFieldType extends AbstractFieldType
{
    public function normalize(mixed $value, array $config, array $context = []): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }

    public function renderInput(string $name, mixed $value, array $config, array $context = []): string
    {
        return '<input type="hidden" name="' . Security::e($name) . '" value="0"><label class="check"><input type="checkbox" name="' . Security::e($name) . '" value="1"' . ($this->normalize($value, $config, $context) ? ' checked' : '') . '> Enabled</label>';
    }
}

