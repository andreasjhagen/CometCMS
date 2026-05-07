<?php

declare(strict_types=1);

namespace CometCMS\Trash;

use CometCMS\Core\Security;
use CometCMS\Storage\JsonStore;

final class TrashStore
{
    private JsonStore $content;

    public function __construct()
    {
        $this->content = new JsonStore(COMET_STORAGE . '/trash/content');
    }

    public function putContent(string $collection, string $id, array $entry): void
    {
        $this->content->write($entry, $collection, $id);
    }

    public function findContent(string $collection, string $id): ?array
    {
        return $this->content->read($collection, $id);
    }

    public function allContent(string $collection): array
    {
        Security::assertSafeName($collection);

        return $this->content->all($collection);
    }

    public function removeContent(string $collection, string $id): void
    {
        $this->content->delete($collection, $id);
    }
}

