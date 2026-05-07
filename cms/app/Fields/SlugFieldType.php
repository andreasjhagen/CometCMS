<?php

declare(strict_types=1);

namespace CometCMS\Fields;

use CometCMS\Core\Security;

final class SlugFieldType extends AbstractFieldType
{
    public function normalize(mixed $value, array $config, array $context = []): string
    {
        return Security::slug((string) $value);
    }
}

