<?php

namespace App\Contracts;

use App\DTO\CategoryData;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function find(int $id): ?Category;
    public function create(CategoryData $data): Category;
    public function update(Category $category, CategoryData $data): Category;
    public function delete(Category $category): void;
    public function getAllAsTree(): array;
}
