<?php

namespace App\Repositories;

use App\Contracts\CategoryRepositoryInterface;
use App\DTO\CategoryData;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Category::withCount([
            'materials as published_count' => function ($q) {
                $q->where('state', 'published');
            },
            'materials as draft_count' => function ($q) {
                $q->where('state', 'draft');
            },
            'materials as trash_count' => function ($q) {
                $q->where('state', 'trash');
            },
        ]);

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'ilike', '%'.$filters['search'].'%')
                    ->orWhere('alias', 'ilike', '%'.$filters['search'].'%');
            });
        }

        return $query->orderBy('lft')->paginate(20);
    }

    public function find(int $id): ?Category
    {
        return Category::find($id);
    }

    public function create(CategoryData $data): Category
    {
        return Category::create($data->toArray());
    }

    public function update(Category $category, CategoryData $data): Category
    {
        $category->update($data->toArray());

        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllAsTree(): array
    {
        $categories = Category::orderBy('lft')->get();

        return $this->buildCategoryTree($categories);
    }

    /**
     * @param  iterable<Category>  $categories
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildCategoryTree(iterable $categories, ?int $parentId = null): array
    {
        $tree = [];

        foreach ($categories as $category) {
            if ($category->parent_id === $parentId) {
                $node = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'alias' => $category->alias,
                    'description' => $category->description,
                    'depth' => $category->depth,
                    'lft' => $category->lft,
                    'rgt' => $category->rgt,
                    'is_active' => $category->is_active,
                    'created_at' => $category->created_at?->toISOString(),
                    'updated_at' => $category->updated_at?->toISOString(),
                    'children' => $this->buildCategoryTree($categories, $category->id),
                ];
                $tree[] = $node;
            }
        }

        return $tree;
    }
}
