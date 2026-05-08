<?php

declare(strict_types=1);

use CometCMS\Webhooks\WebhookSigner;

test('webhook signer creates valid sha256 hmac signature', function (): void {
    $signer = new WebhookSigner();
    $payload = '{"event":"content.updated"}';
    $secret = 'top-secret';

    $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);
    assert_same($expected, $signer->sign($payload, $secret));
});
