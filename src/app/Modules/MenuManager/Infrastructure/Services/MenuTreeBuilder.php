<?php

namespace App\Modules\MenuManager\Infrastructure\Services;

use App\Modules\MenuManager\Domain\Services\MenuTreeBuilderInterface;

class MenuTreeBuilder implements MenuTreeBuilderInterface
{
    public function buildTree(array $items, ?int $parentId = null): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['parent_id'] === $parentId) {
                $children = $this->buildTree($items, $item['id']);
                if ($children) $item['children'] = $children;
                $tree[] = $item;
            }
        }
        return $tree;
    }
}