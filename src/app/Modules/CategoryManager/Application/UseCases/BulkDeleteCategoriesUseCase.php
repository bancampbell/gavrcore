<?php

namespace App\Modules\CategoryManager\Application\UseCases;

use App\Modules\CategoryManager\Domain\Repositories\CategoryRepositoryInterface;

class BulkDeleteCategoriesUseCase
{
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {
    }

    /**
     * @param  array<int>  $ids
     */
    public function execute(array $ids): void
    {
        $this->repository->bulkDelete($ids);
    }
}
