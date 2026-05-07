<?php

declare(strict_types=1);

namespace CometCMS\Core;

final class ValidationException extends \RuntimeException
{
    public function __construct(private readonly array $fields, string $message = 'Validation failed.')
    {
        parent::__construct($message, 422);
    }

    public function fields(): array
    {
        return $this->fields;
    }
}

