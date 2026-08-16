<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\Services\ImageStorageServiceInterface;

class DeleteGalleryUseCase
{
    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
        private readonly ImageStorageServiceInterface $storage,
    ) {}

    public function execute(int $id): void
    {
        $gallery = $this->repository->findByIdWithImages($id);

        if ($gallery) {
            foreach ($gallery->images() as $image) {
                $this->storage->delete($image->imagePath);
            }
        }

        $this->repository->delete($id);
    }
}
