<?php

namespace App\Modules\MenuManager\Application\UseCases;

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

        return $this->buildTree($items);
    }

    private function buildTree($items, ?int $parentId = null): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item->parent_id === $parentId) {
                $children = $this->buildTree($items, $item->id);
                $node = [
                    'id' => $item->id,
                    'title' => $item->title,
                    'link_type' => $item->link_type,
                    'link_value' => $item->link_value,
                    'target' => $item->target,
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
