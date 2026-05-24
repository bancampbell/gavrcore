<?php

namespace App\Services;

use App\Models\Category;

class CategoryTreeService
{
    public function buildTree($categories, $parentId = null): array
    {
        $tree = [];

        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $children = $this->buildTree($categories, $category->id);
                if ($children) {
                    $category->children = $children;
                }
                $tree[] = $category;
            }
        }

        return $tree;
    }

    public function getSelectOptions($categories, $prefix = ''): array
    {
        $options = [];

        foreach ($categories as $category) {
            $options[$category->id] = $prefix . $category->name;
            if ($category->children) {
                $options += $this->getSelectOptions($category->children, $prefix . '— ');
            }
        }

        return $options;
    }

    public function rebuildNestedSet(): void
    {
        $categories = Category::orderBy('parent_id')->orderBy('name')->get();
        $this->rebuildTree($categories);
    }

    private function rebuildTree($categories, $parentId = null, $depth = 0): int
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
