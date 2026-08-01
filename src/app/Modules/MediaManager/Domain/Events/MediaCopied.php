<?php

namespace Modules\MediaManager\Domain\Events;

readonly class MediaCopied
{
    public function __construct(
        public int $userId,
        public string $path,
    ) {}
}
