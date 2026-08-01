<?php

namespace Modules\MediaManager\Application\DTO;

class CreateFolderData
{
    public function __construct(
        public readonly string $name,
        public readonly string $path,
    ) {}
}
