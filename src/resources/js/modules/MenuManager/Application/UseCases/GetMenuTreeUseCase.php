<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;

class GetMenuTreeUseCase
{
    public function execute(string $alias): array
    {
        $menuType = MenuTypeModel::where('alias', $alias)->where('status', true)->first();
        if (! $menuType) return [];

        $items = $menuType->activeItems()
            ->with(['children' => fn($q) => $q->where('status', true)->orderBy('ordering')])
            ->whereNull('parent_id')
            ->orderBy('ordering')
            ->get();

        return $this->buildTree($items);
    }

    private function buildTree($items): array
    {
        $tree = [];
        foreach ($items as $item) {
            $tree[] = [
                'id' => $item->id,
                'title' => $item->title,
                'link_type' => $item->link_type,
                'link_value' => $item->link_value,
                'target' => $item->target,
                'children' => $this->buildTree($item->children),
            ];
        }
        return $tree;
    }
}