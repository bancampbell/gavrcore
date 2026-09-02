<?php

namespace App\Modules\CategoryManager\Application\UseCases;

use App\Modules\CategoryManager\Domain\Repositories\CategoryRepositoryInterface;

class GetAllCategoriesUseCase
{
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function execute(): array
    {
        return $this->repository->getAllForSelect();
    }
}
