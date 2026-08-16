<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;

class UpdateImageUseCase
{
    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
    ) {}

    public function execute(int $galleryId, int $imageId, array $data): array
    {
        $image = $this->repository->findImageById($imageId);

        if (!$image || $image->galleryId !== $galleryId) {
            throw new \RuntimeException('Image not found');
        }

        $image = $this->repository->updateImage($imageId, $data);
        return $image->toArray();
    }
}
