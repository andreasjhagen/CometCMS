<?php

declare(strict_types=1);

namespace CometCMS\Core;

final class ApiResponder
{
    public function __construct(private readonly Http $http)
    {
    }

    public function data(mixed $data, int $status = 200, array $meta = []): never
    {
        $payload = ['data' => $data];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        $this->http->json($payload, $status);
    }

    public function error(string $code, string $message, int $status, array $fields = []): never
    {
        $payload = [
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        if ($fields !== []) {
            $payload['error']['fields'] = $fields;
        }

        $this->http->json($payload, $status);
    }
}

