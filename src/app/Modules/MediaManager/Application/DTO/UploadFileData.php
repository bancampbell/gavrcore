<?php

namespace Modules\MediaManager\Application\DTO;

class UploadFileData
{
    /**
     * @param array<int, array{path: string, name: string}> $files
     */
    public function __construct(
        public readonly array $files,
        public readonly string $path,
    ) {}
}
