<?php

declare(strict_types=1);

namespace CometCMS\Webhooks;

final class WebhookSigner
{
    public function sign(string $payload, string $secret): string
    {
        return 'sha256=' . hash_hmac('sha256', $payload, $secret);
    }
}

