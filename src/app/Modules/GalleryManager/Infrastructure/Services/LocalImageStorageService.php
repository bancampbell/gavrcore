<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Infrastructure\Services;

use App\Modules\GalleryManager\Domain\Services\ImageStorageServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LocalImageStorageService implements ImageStorageServiceInterface
{
    public function store(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');
        return '/storage/' . $path;
    }

    public function delete(string $path): void
    {
        $relativePath = str_replace('/storage/', '', $path);
        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
