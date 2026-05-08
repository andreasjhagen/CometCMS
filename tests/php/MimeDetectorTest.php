<?php

declare(strict_types=1);

use CometCMS\Core\MimeDetector;

test('mime detector detects jpeg and pdf from file content', function (): void {
    $jpgPath = COMET_STORAGE . '/media/sample.jpg';
    $pdfPath = COMET_STORAGE . '/media/sample.pdf';

    file_put_contents($jpgPath, hex2bin('ffd8ffe000104a46494600010100000100010000ffd9'));
    file_put_contents($pdfPath, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n");

    assert_same('image/jpeg', MimeDetector::detect($jpgPath));
    assert_same('application/pdf', MimeDetector::detect($pdfPath));
});

test('mime detector returns octet-stream for unknown extensions', function (): void {
    set_error_handler(static fn(): bool => true);
    $detected = MimeDetector::detect(COMET_STORAGE . '/media/missing-unknown', 'unknown.zzz');
    restore_error_handler();

    assert_same('application/octet-stream', $detected);
});

test('mime detector falls back to extension map when file probing fails', function (): void {
    set_error_handler(static fn(): bool => true);
    $detected = MimeDetector::detect(COMET_STORAGE . '/media/missing-file', 'document.pdf');
    restore_error_handler();

    assert_same('application/pdf', $detected);
});
