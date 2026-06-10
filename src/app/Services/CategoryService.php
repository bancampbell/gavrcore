<?php

namespace App\Services;

use App\Contracts\CategoryRepositoryInterface;
use App\DTO\CategoryData;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function __construct(
        protected CategoryRepositoryInterface $repository,
        protected CategoryTreeService $treeService
    ) {}

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Category>
     */
    public function getPaginated(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    /**
     * @return array<int, string>
     */
    public function getAllForSelect(): array
    {
        $categories = Category::orderBy('lft')->get();
        return $this->treeService->getSelectOptions($categories);
    }

    public function create(CategoryData $data): Category
    {
        return $this->repository->create($data);
    }

    public function update(Category $category, CategoryData $data): Category
    {
        return $this->repository->update($category, $data);
    }

    public function delete(Category $category): void
    {
        $this->repository->delete($category);
    }

    public function find(int $id): ?Category
    {
        return $this->repository->find($id);
    }
}
