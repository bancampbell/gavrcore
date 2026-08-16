<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\Services\ImageStorageServiceInterface;

class DeleteImageUseCase
{
    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
        private readonly ImageStorageServiceInterface $storage,
    ) {}

    public function execute(int $galleryId, int $imageId): void
    {
        $image = $this->repository->findImageById($imageId);

        if (!$image || $image->galleryId !== $galleryId) {
            throw new \RuntimeException('Image not found');
        }

        $this->storage->delete($image->imagePath);
        $this->repository->deleteImage($imageId);
    }
}
