<?php

declare(strict_types=1);

use CometCMS\Core\Security;

test('html output is escaped with quotes and substitutions', function (): void {
    assert_same(
        '&lt;a href=&quot;/admin&quot;&gt;Tom &amp; Bob&lt;/a&gt;',
        Security::e('<a href="/admin">Tom & Bob</a>')
    );
});

test('slugs are normalized for storage-safe names', function (): void {
    assert_same('hello-cms_world', Security::slug(' Hello, CMS_WORLD!! '));
});

test('safe names reject path traversal characters', function (): void {
    assert_throws(InvalidArgumentException::class, static fn() => Security::assertSafeName('../users'));
});

test('uuid creates valid version 4 ids', function (): void {
    assert_matches(
        '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
        Security::uuid()
    );
});
