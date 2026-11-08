<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\DTO\GalleryFiltersData;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetGalleryListUseCase
{
    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
    ) {}

    public function execute(?GalleryFiltersData $filters = null): LengthAwarePaginator
    {
        $filters ??= new GalleryFiltersData();

        return $this->repository->paginate(
            $filters->toArray(),
            $filters->perPage,
            $filters->page,
        );
    }
}
