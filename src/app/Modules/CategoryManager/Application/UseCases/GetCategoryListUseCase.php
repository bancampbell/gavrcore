<?php

namespace App\Modules\CategoryManager\Application\UseCases;

use App\Modules\CategoryManager\Application\DTO\CategoryFiltersData;
use App\Modules\CategoryManager\Domain\Repositories\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetCategoryListUseCase
{
    public function __construct(
        protected CategoryRepositoryInterface $repository
    ) {
    }

    /**
     * @return LengthAwarePaginator<int, \App\Modules\CategoryManager\Infrastructure\Models\CategoryModel>
     */
    public function execute(CategoryFiltersData $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters->toArray());
    }
}
