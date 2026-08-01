<?php

namespace Modules\MediaManager\Application\DTO;

class DeleteItemData
{
    public function __construct(
        public readonly string $path,
    ) {}
}
