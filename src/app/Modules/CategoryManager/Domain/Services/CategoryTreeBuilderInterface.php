<?php

namespace App\Modules\CategoryManager\Domain\Services;

use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;

interface CategoryTreeBuilderInterface
{
    /**
     * @param  iterable<CategoryModel>  $categories
     * @return array<int, array<string, mixed>>
     */
    public function buildTree(iterable $categories, ?int $parentId = null): array;

    /**
     * @param  iterable<CategoryModel>  $categories
     * @return array<int, string>
     */
    public function getSelectOptions(iterable $categories, string $prefix = ''): array;

    public function rebuildNestedSet(): void;
}
