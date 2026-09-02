<?php

namespace App\Modules\CategoryManager\Application\UseCases;

use App\Modules\CategoryManager\Domain\Repositories\CategoryRepositoryInterface;
use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;

class DeleteCategoryUseCase
{
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {
    }

    public function execute(CategoryModel $category): void
    {
        $this->repository->delete($category);
    }
}
