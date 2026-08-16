<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\DTO\GalleryFiltersData;
use App\Modules\GalleryManager\Domain\Entities\Gallery;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use Illuminate\Support\Collection;

class GetGalleryListUseCase
{
    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
    ) {}

    public function execute(?GalleryFiltersData $filters = null): Collection
    {
        $galleries = $this->repository->getAllWithImageCount();

        if (!$filters) {
            return $galleries;
        }

        return $galleries->filter(function (Gallery $gallery) use ($filters) {
            if ($filters->search && !str_contains(strtolower($gallery->title), strtolower($filters->search))) {
                return false;
            }
            if ($filters->type && $gallery->type->value !== $filters->type) {
                return false;
            }
            if ($filters->status !== null && $gallery->status->value !== (int) $filters->status) {
                return false;
            }
            return true;
        });
    }
}
