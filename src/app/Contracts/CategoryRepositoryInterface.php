<?php

namespace App\Contracts;

use App\DTO\CategoryData;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Category>
     */
    public function paginate(array $filters): LengthAwarePaginator;

    public function find(int $id): ?Category;

    public function create(CategoryData $data): Category;

    public function update(Category $category, CategoryData $data): Category;

    public function delete(Category $category): void;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllAsTree(): array;
}
