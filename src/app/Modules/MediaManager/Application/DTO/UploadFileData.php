<?php

namespace Modules\MediaManager\Application\DTO;

class UploadFileData
{
    public function __construct(
        public readonly array $filePaths,
        public readonly string $path,
    ) {}
}
