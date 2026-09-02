<?php

namespace App\Modules\CategoryManager\Domain\Repositories;

use App\Modules\CategoryManager\Application\DTO\CategoryData;
use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     *
     * @return LengthAwarePaginator<int, CategoryModel>
     */
    public function paginate(array $filters): LengthAwarePaginator;

    public function find(int $id): ?CategoryModel;

    public function create(CategoryData $data): CategoryModel;

    public function update(CategoryModel $category, CategoryData $data): CategoryModel;

    public function delete(CategoryModel $category): void;

    /**
     * @param  array<int>  $ids
     */
    public function bulkDelete(array $ids): void;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllAsTree(): array;

    /**
     * @return array<int, string>
     */
    public function getAllForSelect(): array;
}
