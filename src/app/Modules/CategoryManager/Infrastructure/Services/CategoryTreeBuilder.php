<?php

namespace App\Modules\CategoryManager\Infrastructure\Services;

use App\Modules\CategoryManager\Domain\Services\CategoryTreeBuilderInterface;
use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use Illuminate\Support\Facades\DB;

class CategoryTreeBuilder implements CategoryTreeBuilderInterface
{
    /**
     * @param  iterable<CategoryModel>  $categories
     *
     * @return array<int, array{id: int, name: string, children: array<int, array<string, mixed>>}>
     */
    public function buildTree(iterable $categories, ?int $parentId = null): array
    {
        $tree = [];

        foreach ($categories as $category) {
            if ($category->parent_id === $parentId) {
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
     * @param  iterable<CategoryModel>  $categories
     *
     * @return array<int, string>
     */
    public function getSelectOptions(iterable $categories, string $prefix = ''): array
    {
        $options = [];

        foreach ($categories as $category) {
            $options[$category->id] = $prefix.$category->name;
            $children = $category->children()->get();
            if ($children->isNotEmpty()) {
                $options += $this->getSelectOptions($children, $prefix.'— ');
            }
        }

        return $options;
    }

    public function rebuildNestedSet(): void
    {
        DB::transaction(function () {
            $categories = CategoryModel::orderBy('parent_id')->orderBy('name')->lockForUpdate()->get();
            $this->rebuildTree($categories);
        });
    }

    /**
     * @param  iterable<CategoryModel>  $categories
     */
    private function rebuildTree(iterable $categories, ?int $parentId = null, int $depth = 0, int $lft = 1): int
    {
        foreach ($categories as $category) {
            if ($category->parent_id === $parentId) {
                $category->lft = $lft;
                $category->depth = $depth;
                $category->save();

                $lft = $this->rebuildTree($categories, $category->id, $depth + 1, $lft + 1);
                $category->rgt = $lft;
                $category->save();
                $lft++;
            }
        }

        return $lft;
    }
}
