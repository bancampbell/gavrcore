<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\DTO\PaginatedResult;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;

final readonly class GetTrashMaterialsUseCase
{
    public function __construct(
        private MaterialRepositoryInterface $repository,
    ) {}

    public function execute(int $perPage = 10): PaginatedResult
    {
        return $this->repository->getTrashPaginated($perPage);
    }
}
