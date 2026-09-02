<?php

namespace App\Modules\CategoryManager\Application\UseCases;

use App\Modules\CategoryManager\Application\DTO\CategoryData;
use App\Modules\CategoryManager\Domain\Repositories\CategoryRepositoryInterface;
use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;

class UpdateCategoryUseCase
{
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {
    }

    public function execute(CategoryModel $category, CategoryData $data): CategoryModel
    {
        return $this->repository->update($category, $data);
    }
}
