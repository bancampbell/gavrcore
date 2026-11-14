<?php

namespace App\Modules\MaterialManager\Infrastructure\Services;

use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use App\Modules\MaterialManager\Domain\Services\CategoryServiceInterface;

class CategoryService implements CategoryServiceInterface
{
    public function findById(int $id): ?object
    {
        return CategoryModel::find($id);
    }

    public function findBySlug(string $slug): ?object
    {
        return CategoryModel::where('alias', $slug)->first();
    }

    public function getAll(): array
    {
        return CategoryModel::all()->toArray();
    }

    public function findVisibleChildrenWithMaterials(int $parentId, array $accessLevels): array
    {
        // Пункты списка публичной страницы категории: только активные дочерние,
        // в которых есть хотя бы один опубликованный материал с разрешённым
        // уровнем доступа. Пустые (и «только черновики») не выводятся.
        // Один запрос с EXISTS-подзапросом — без N+1.
        return CategoryModel::query()
            ->where('parent_id', $parentId)
            ->where('is_active', true)
            ->whereHas('materials', function ($query) use ($accessLevels) {
                $query->where('state', 'published')
                    ->whereIn('access', $accessLevels);
            })
            ->orderBy('lft')
            ->get(['id', 'name', 'alias', 'description'])
            ->toArray();
    }
}
