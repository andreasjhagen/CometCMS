<?php

declare(strict_types=1);

use CometCMS\Media\MediaCategory;
use CometCMS\Media\MediaFileType;

test('media file type helper classifies supported groups', function (): void {
    assert_true(MediaFileType::matches('hero.JPG', 'images'));
    assert_true(MediaFileType::matches('movie.webm', 'video'));
    assert_true(MediaFileType::matches('song.flac', 'audio'));
    assert_true(MediaFileType::matches('guide.pdf', 'documents'));
    assert_true(MediaFileType::matches('bundle.zip', 'archives'));
    assert_true(MediaFileType::matches('unknown.bin', 'other'));
    assert_false(MediaFileType::matches('hero.jpg', 'documents'));
});

test('media category helper normalizes paths and nested matching', function (): void {
    assert_same('Images / Heroes', MediaCategory::normalize(' Images// Heroes '));
    assert_same(['Images', 'Images / Heroes'], MediaCategory::pathsFor('Images / Heroes'));
    assert_true(MediaCategory::matches('Images / Heroes / Homepage', 'Images / Heroes'));
    assert_false(MediaCategory::matches('Documents', 'Images'));
    assert_same('Assets / Heroes', MediaCategory::renamePath('Images / Heroes', 'Images', 'Assets'));
    assert_same(['Assets', 'Images', 'Images / Heroes'], MediaCategory::sort(['Images / Heroes', 'Assets', 'Images']));
});
