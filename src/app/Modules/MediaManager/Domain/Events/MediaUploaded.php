<?php

namespace Modules\MediaManager\Domain\Events;

readonly class MediaUploaded
{
    public function __construct(
        public int $userId,
        public string $path,
    ) {}
}
