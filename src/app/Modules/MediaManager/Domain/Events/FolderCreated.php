<?php

namespace Modules\MediaManager\Domain\Events;

readonly class FolderCreated
{
    public function __construct(
        public int $userId,
        public string $path,
    ) {}
}
