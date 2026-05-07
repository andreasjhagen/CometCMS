<?php

declare(strict_types=1);

use CometCMS\Backups\BackupService;

test('backup notes protect backups from deletion until cleared', function (): void {
    $name = 'example-backup.zip';
    $path = COMET_STORAGE . '/backups/' . $name;
    file_put_contents($path, 'zip-placeholder');

    $service = new BackupService();
    $backup = $service->setNote($name, 'Keep before launch', ['id' => 'admin']);

    assert_same('Keep before launch', $backup['note']);
    assert_true($backup['is_protected']);
    assert_file_exists_at($path . '.meta.json');
    assert_throws(RuntimeException::class, static fn() => $service->delete($name));
    assert_file_exists_at($path);

    $backup = $service->setNote($name, '   ', ['id' => 'admin']);

    assert_same('', $backup['note']);
    assert_false($backup['is_protected']);
    assert_false(is_file($path . '.meta.json'));

    $service->delete($name);

    assert_false(is_file($path));
});
