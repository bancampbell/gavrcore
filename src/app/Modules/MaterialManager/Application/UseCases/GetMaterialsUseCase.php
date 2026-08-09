<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\DTO\MaterialFiltersData;
use App\Modules\MaterialManager\Application\DTO\PaginatedResult;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;

final readonly class GetMaterialsUseCase
{
    public function __construct(
        private MaterialRepositoryInterface $repository,
    ) {}

    public function execute(MaterialFiltersData $filters): PaginatedResult
    {
        return $this->repository->paginate(
            $filters->toArray(),
            $filters->perPage
        );
    }
}
