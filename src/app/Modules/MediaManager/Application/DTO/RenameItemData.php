<?php

namespace Modules\MediaManager\Application\DTO;

class RenameItemData
{
    public function __construct(
        public readonly string $oldPath,
        public readonly string $newName,
    ) {}
}
