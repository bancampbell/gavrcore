<?php

namespace Modules\MediaManager\Application\DTO;

class CopyItemData
{
    public function __construct(
        public readonly string $path,
    ) {}
}
