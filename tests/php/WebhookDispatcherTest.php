<?php

declare(strict_types=1);

use CometCMS\Logging\Logger;
use CometCMS\Webhooks\WebhookDispatcher;

test('webhook dispatcher only sends matching enabled webhook events', function (): void {
    global $cometConfig;

    $captureFile = COMET_STORAGE . '/webhook-capture.log';
    $routerPath = COMET_STORAGE . '/webhook-router.php';
    file_put_contents($routerPath, "<?php\nfile_put_contents('" . addslashes($captureFile) . "', file_get_contents('php://input') . PHP_EOL, FILE_APPEND);\nhttp_response_code(204);\n");

    $port = 20080 + random_int(0, 2000);
    $pid = (int) trim((string) shell_exec(sprintf(
        'php -S 127.0.0.1:%d %s >/dev/null 2>&1 & echo $!',
        $port,
        escapeshellarg($routerPath)
    )));

    if ($pid <= 0) {
        throw new RuntimeException('Failed to start temporary webhook server.');
    }

    $previous = $cometConfig['webhooks'] ?? [];

    try {
        usleep(300000);

        $cometConfig['webhooks'] = [
            [
                'url' => 'http://127.0.0.1:' . $port,
                'secret' => 'abc123',
                'enabled' => true,
                'events' => ['content.updated'],
            ],
            [
                'url' => 'http://127.0.0.1:' . $port,
                'enabled' => false,
                'events' => ['content.updated'],
            ],
            [
                'url' => 'http://127.0.0.1:' . $port,
                'enabled' => true,
                'events' => ['content.created'],
            ],
        ];

        $dispatcher = new WebhookDispatcher(new Logger(COMET_STORAGE . '/logs/webhook-dispatch.log'));
        $dispatcher->dispatch('content.updated', ['id' => 'entry-1']);

        $captured = trim((string) file_get_contents($captureFile));
        $payload = json_decode($captured, true);

        assert_same('content.updated', $payload['event'] ?? null);
        assert_same('entry-1', $payload['data']['id'] ?? null);
    } finally {
        $cometConfig['webhooks'] = $previous;
        shell_exec('kill ' . $pid);
    }
});

test('webhook dispatcher sendWebhook returns false for missing url', function (): void {
    $dispatcher = new WebhookDispatcher(new Logger(COMET_STORAGE . '/logs/webhook-send.log'));

    assert_false($dispatcher->sendWebhook(['events' => ['content.updated']], 'content.updated', ['id' => 'x']));
});
