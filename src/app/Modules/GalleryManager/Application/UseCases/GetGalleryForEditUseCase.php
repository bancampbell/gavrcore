<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;

class GetGalleryForEditUseCase
{
    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
    ) {}

    public function execute(int $id): ?array
    {
        $gallery = $this->repository->findByIdWithImages($id);
        return $gallery?->toArray();
    }
}
