<?php

namespace App\Services;

use App\Models\MenuType;
use App\Models\MenuItem;
use Illuminate\Support\Collection;

class MenuService
{
    public function getMenuTree(string $alias): array
    {
        $menuType = MenuType::where('alias', $alias)
            ->where('status', true)
            ->first();

        if (!$menuType) {
            return [];
        }

        $items = $menuType->activeItems()
            ->with(['children' => function ($query) {
                $query->where('status', true)->orderBy('ordering');
            }])
            ->whereNull('parent_id')
            ->orderBy('ordering')
            ->get();

        return $this->buildTree($items);
    }

    private function buildTree(Collection $items): array
    {
        $tree = [];

        foreach ($items as $item) {
            $node = [
                'id' => $item->id,
                'title' => $item->title,
                'link_type' => $item->link_type,
                'link_value' => $item->link_value,
                'target' => $item->target,
                'children' => $this->buildTree($item->children),
            ];
            $tree[] = $node;
        }

        return $tree;
    }
}
