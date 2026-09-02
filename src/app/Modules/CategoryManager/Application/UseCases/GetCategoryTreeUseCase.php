<?php

namespace App\Modules\CategoryManager\Application\UseCases;

use App\Modules\CategoryManager\Domain\Repositories\CategoryRepositoryInterface;

class GetCategoryTreeUseCase
{
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(): array
    {
        return $this->repository->getAllAsTree();
    }
}
