<?php

namespace App\Modules\CategoryManager\Application\UseCases;

use App\Modules\CategoryManager\Application\DTO\CategoryData;
use App\Modules\CategoryManager\Domain\Repositories\CategoryRepositoryInterface;
use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;

class CreateCategoryUseCase
{
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {
    }

    public function execute(CategoryData $data): CategoryModel
    {
        return $this->repository->create($data);
    }
}
