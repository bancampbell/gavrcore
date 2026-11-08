<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MaterialManager\Infrastructure\Models\MaterialModel;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;

class GetMenuTreeUseCase
{
    public function execute(string $alias): array
    {
        $menuType = MenuTypeModel::where('alias', $alias)->where('status', true)->first();
        if (! $menuType) {
            return [];
        }

        $query = MenuItemModel::where('menu_type_id', $menuType->id)
            ->where('status', true);

        $isAuthenticated = auth()->check();

        $query->where(function ($q) use ($isAuthenticated) {
            $q->where('access', 'all');

            if ($isAuthenticated) {
                $q->orWhere('access', 'registered');
            } else {
                $q->orWhere('access', 'guest');
            }
        });

        $currentLanguage = app()->getLocale();

        $query->where(function ($q) use ($currentLanguage) {
            $q->where('language', 'all')
                ->orWhere('language', $currentLanguage);
        });

        $items = $query->orderBy('ordering')->get();

        return $this->buildTree($items, null, $this->resolveMaterialUrls($items));
    }

    private function resolveMaterialUrls($items): array
    {
        $slugs = $items->where('link_type', 'material')
            ->pluck('link_value')
            ->filter()
            ->unique()
            ->values();

        if ($slugs->isEmpty()) {
            return [];
        }

        // Один запрос на все пункты material: slug → готовый URL материала
        return MaterialModel::whereIn('slug', $slugs)
            ->with('category:id,alias')
            ->get(['slug', 'category_id'])
            ->mapWithKeys(fn (MaterialModel $material) => [
                $material->slug => $material->category
                    ? '/'.$material->category->alias.'/'.$material->slug
                    : '/'.$material->slug,
            ])
            ->all();
    }

    private function resolveNodeUrl($item, array $materialUrls): ?string
    {
        return match ($item->link_type) {
            'material' => $materialUrls[$item->link_value] ?? null,
            'category' => '/category/'.$item->link_value,
            'url', 'external' => $item->link_value,
            default => null,
        };
    }

    private function buildTree($items, ?int $parentId = null, array $materialUrls = []): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item->parent_id === $parentId) {
                $children = $this->buildTree($items, $item->id, $materialUrls);
                $node = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'link_type' => $item->link_type,
                    'link_value' => $item->link_value,
                    'target' => $item->target,
                    'url' => $this->resolveNodeUrl($item, $materialUrls),
                ];
                if ($children) {
                    $node['children'] = $children;
                }
                $tree[] = $node;
            }
        }

        return $tree;
    }
}
