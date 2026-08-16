<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\Services\ImageStorageServiceInterface;
use Illuminate\Http\UploadedFile;

class UploadImageUseCase
{
    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
        private readonly ImageStorageServiceInterface $storage,
    ) {}

    public function execute(int $galleryId, UploadedFile $file, ?string $title): array
    {
        $path = $this->storage->store($file, "galleries/{$galleryId}");

        $image = $this->repository->addImage($galleryId, [
            'image_path' => $path,
            'title' => $title ?? $file->getClientOriginalName(),
            'ordering' => 0,
            'status' => true,
        ]);

        return $image->toArray();
    }
}
