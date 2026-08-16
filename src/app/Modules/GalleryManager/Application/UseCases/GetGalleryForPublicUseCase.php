<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;

class GetGalleryForPublicUseCase
{
    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
    ) {}

    public function execute(int $id): ?array
    {
        $gallery = $this->repository->findByIdWithImages($id);

        if (!$gallery || !$gallery->isPublished()) {
            return null;
        }

        return $gallery->toArray();
    }
}
