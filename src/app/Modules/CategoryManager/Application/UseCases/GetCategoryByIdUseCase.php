<?php

namespace App\Modules\CategoryManager\Application\UseCases;

use App\Modules\CategoryManager\Domain\Repositories\CategoryRepositoryInterface;
use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;

class GetCategoryByIdUseCase
{
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {
    }

    public function execute(int $id): ?CategoryModel
    {
        return $this->repository->find($id);
    }
}
