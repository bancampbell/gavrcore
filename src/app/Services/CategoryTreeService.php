<?php

namespace App\Services;

use App\Models\Category;

class CategoryTreeService
{
    /**
     * @param iterable<Category> $categories
     * @return array<int, array{id: int, name: string, children: array<int, array<string, mixed>>}>
     */
    public function buildTree(iterable $categories, ?int $parentId = null): array
    {
        $tree = [];

        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $node = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'children' => $this->buildTree($categories, $category->id),
                ];
                $tree[] = $node;
            }
        }

        return $tree;
    }

    /**
     * @param iterable<Category> $categories
     * @return array<int, string>
     */
    public function getSelectOptions(iterable $categories, string $prefix = ''): array
    {
        $options = [];

        foreach ($categories as $category) {
            $options[$category->id] = $prefix . $category->name;
            // Здесь нужно получать детей из отношения, а не из свойства
            $children = $category->children()->get();
            if ($children->isNotEmpty()) {
                $options += $this->getSelectOptions($children, $prefix . '— ');
            }
        }

        return $options;
    }

    public function rebuildNestedSet(): void
    {
        $categories = Category::orderBy('parent_id')->orderBy('name')->get();
        $this->rebuildTree($categories);
    }

    /**
     * @param iterable<Category> $categories
     */
    private function rebuildTree(iterable $categories, ?int $parentId = null, int $depth = 0): int
    {
        $lft = 1;

        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $category->lft = $lft;
                $category->depth = $depth;
                $category->save();

                $lft = $this->rebuildTree($categories, $category->id, $depth + 1);
                $category->rgt = $lft;
                $category->save();
                $lft++;
            }
        }

        return $lft;
    }
}
