<?php

namespace Modules\MediaManager\Domain\Events;

readonly class MediaRenamed
{
    public function __construct(
        public int $userId,
        public string $oldPath,
        public string $newPath,
    ) {}
}
