<?php

declare(strict_types=1);

use CometCMS\Trash\TrashStore;

test('trash store supports content CRUD operations', function (): void {
    $trash = new TrashStore();
    $entry = ['id' => 'entry-1', 'title' => 'Deleted Post'];

    $trash->putContent('posts', 'entry-1', $entry);
    assert_same($entry, $trash->findContent('posts', 'entry-1'));

    $all = $trash->allContent('posts');
    assert_same(1, count($all));
    assert_same('entry-1', $all[0]['id'] ?? null);

    $trash->removeContent('posts', 'entry-1');
    assert_null($trash->findContent('posts', 'entry-1'));
});
