<?php

declare(strict_types=1);

namespace CometCMS\Fields;

interface FieldType
{
    public function validate(mixed $value, array $config, array $context = []): array;

    public function normalize(mixed $value, array $config, array $context = []): mixed;

    public function renderInput(string $name, mixed $value, array $config, array $context = []): string;
}

