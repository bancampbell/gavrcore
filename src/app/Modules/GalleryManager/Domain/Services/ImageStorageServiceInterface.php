<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Domain\Services;

use Illuminate\Http\UploadedFile;

interface ImageStorageServiceInterface
{
    public function store(UploadedFile $file, string $directory): string;
    public function delete(string $path): void;
}
