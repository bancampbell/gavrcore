<?php

namespace Modules\MediaManager\Domain\Events;

readonly class MediaDeleted
{
    public function __construct(
        public int $userId,
        public string $path,
    ) {}
}
