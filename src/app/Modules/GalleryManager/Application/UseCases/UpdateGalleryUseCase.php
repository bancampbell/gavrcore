<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\DTO\UpdateGalleryData;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;

class UpdateGalleryUseCase
{
    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateGalleryData $data): void
    {
        $gallery = $this->repository->findById($id);

        if (!$gallery) {
            throw new \RuntimeException('Gallery not found');
        }

        $gallery->update(
            title: $data->title,
            type: $data->type,
            settings: $data->settings,
            status: $data->status ? GalleryStatus::PUBLISHED : GalleryStatus::DRAFT,
        );

        $this->repository->update($gallery);
    }
}
