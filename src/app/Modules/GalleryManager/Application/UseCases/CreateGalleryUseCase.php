<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\DTO\CreateGalleryData;
use App\Modules\GalleryManager\Domain\Entities\Gallery;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;

class CreateGalleryUseCase
{
    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
    ) {}

    public function execute(CreateGalleryData $data): Gallery
    {
        $gallery = Gallery::create(
            title: $data->title,
            type: $data->type,
            settings: $data->settings,
            status: $data->status ? GalleryStatus::PUBLISHED : GalleryStatus::DRAFT,
        );

        return $this->repository->create($gallery);
    }
}
