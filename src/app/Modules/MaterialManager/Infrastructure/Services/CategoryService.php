<?php

namespace App\Modules\MaterialManager\Infrastructure\Services;

use App\Models\Category;
use App\Modules\MaterialManager\Domain\Services\CategoryServiceInterface;

class CategoryService implements CategoryServiceInterface
{
    public function findById(int $id): ?object
    {
        return Category::find($id);
    }

    public function findBySlug(string $slug): ?object
    {
        return Category::where('alias', $slug)->first();
    }

    public function getAll(): array
    {
        return Category::all()->toArray();
    }
}
