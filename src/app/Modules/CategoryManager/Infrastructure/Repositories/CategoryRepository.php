<?php

namespace App\Modules\CategoryManager\Infrastructure\Repositories;

use App\Modules\CategoryManager\Application\DTO\CategoryData;
use App\Modules\CategoryManager\Domain\Repositories\CategoryRepositoryInterface;
use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use App\Modules\CategoryManager\Infrastructure\Services\CategoryTreeBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        protected CategoryTreeBuilder $treeBuilder
    ) {
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = CategoryModel::withCount([
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
            $search = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE LOWER(?)', [$search])
                    ->orWhereRaw('LOWER(alias) LIKE LOWER(?)', [$search]);
            });
        }

        if (isset($filters['parent_id']) && $filters['parent_id'] !== null) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== null) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('lft')->paginate(config('category-manager.per_page', 20));
    }

    public function find(int $id): ?CategoryModel
    {
        return CategoryModel::find($id);
    }

    public function create(CategoryData $data): CategoryModel
    {
        $attempts = 0;
        $maxAttempts = 5;

        while ($attempts < $maxAttempts) {
            try {
                return DB::transaction(function () use ($data) {
                    $array = $data->toArray();

                    if (empty($array['alias']) && ! empty($data->name)) {
                        $array['alias'] = $this->generateUniqueAlias(Str::slug($data->name));
                    }

                    $array['is_active'] = $array['is_active'] ?? true;

                    $model = new CategoryModel($array);
                    $model->lft = 0;
                    $model->rgt = 0;
                    $model->depth = 0;
                    $model->save();

                    $this->treeBuilder->rebuildNestedSet();

                    return $model->fresh();
                });
            } catch (QueryException $e) {
                if ($this->isUniqueViolation($e)) {
                    $attempts++;
                    if ($attempts >= $maxAttempts) {
                        throw $e;
                    }
                    continue;
                }
                throw $e;
            }
        }

        throw new \RuntimeException('Failed to create category after '.$maxAttempts.' attempts');
    }

    public function update(CategoryModel $category, CategoryData $data): CategoryModel
    {
        return DB::transaction(function () use ($category, $data) {
            $oldParentId = $category->parent_id;
            $category->update($data->toArray());

            if ($data->hasField('parent_id') && $oldParentId !== $data->parent_id) {
                $this->treeBuilder->rebuildNestedSet();
            }

            return $category->fresh();
        });
    }

    public function delete(CategoryModel $category): void
    {
        DB::transaction(function () use ($category) {
            $fresh = CategoryModel::where('id', $category->id)
                ->lockForUpdate()
                ->firstOrFail();

            $descendants = CategoryModel::where('lft', '>', $fresh->lft)
                ->where('rgt', '<', $fresh->rgt)
                ->orderByDesc('depth')
                ->lockForUpdate()
                ->get();

            $idsToCheck = $descendants->pluck('id')->push($fresh->id);

            if (CategoryModel::whereIn('id', $idsToCheck)->whereHas('materials')->exists()) {
                throw new \RuntimeException('Невозможно удалить категорию, содержащую материалы');
            }

            foreach ($descendants as $descendant) {
                $descendant->delete();
            }

            try {
                $fresh->delete();
            } catch (QueryException $e) {
                if ($this->isForeignKeyViolation($e)) {
                    throw new \RuntimeException('Невозможно удалить категорию, содержащую материалы');
                }
                throw $e;
            }

            $this->treeBuilder->rebuildNestedSet();
        });
    }

    public function bulkDelete(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            $categories = CategoryModel::whereIn('id', $ids)
                ->orderByDesc('depth')
                ->lockForUpdate()
                ->get();

            if ($categories->isEmpty()) {
                return;
            }

            $allIds = collect();

            foreach ($categories as $category) {
                $allIds->push($category->id);

                $descendants = CategoryModel::where('lft', '>', $category->lft)
                    ->where('rgt', '<', $category->rgt)
                    ->orderByDesc('depth')
                    ->lockForUpdate()
                    ->pluck('id');

                $allIds = $allIds->merge($descendants);
            }

            $allIds = $allIds->unique()->values();

            if (CategoryModel::whereIn('id', $allIds)->whereHas('materials')->exists()) {
                throw new \RuntimeException('Невозможно удалить категории, содержащие материалы');
            }

            $toDelete = CategoryModel::whereIn('id', $allIds)
                ->orderByDesc('depth')
                ->lockForUpdate()
                ->get();

            try {
                foreach ($toDelete as $item) {
                    $item->delete();
                }
            } catch (QueryException $e) {
                if ($this->isForeignKeyViolation($e)) {
                    throw new \RuntimeException('Невозможно удалить категории, содержащие материалы');
                }
                throw $e;
            }

            $this->treeBuilder->rebuildNestedSet();
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllAsTree(): array
    {
        $categories = CategoryModel::orderBy('lft')->get();

        return $this->buildCategoryTree($categories);
    }

    /**
     * @return array<int, string>
     */
    public function getAllForSelect(): array
    {
        $categories = CategoryModel::orderBy('lft')->get();

        return $this->treeBuilder->getSelectOptions($categories);
    }

    /**
     * @param  iterable<CategoryModel>  $categories
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

    private function generateUniqueAlias(string $base): string
    {
        $alias = $base;
        $counter = 1;

        while (CategoryModel::where('alias', $alias)->exists()) {
            $alias = $base . '-' . $counter;
            $counter++;
        }

        return $alias;
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        $previous = $e->getPrevious();
        if ($previous instanceof \PDOException) {
            $code = (string) $previous->getCode();

            return in_array($code, ['23505', '23000'], true);
        }

        return false;
    }

    private function isForeignKeyViolation(QueryException $e): bool
    {
        $previous = $e->getPrevious();
        if ($previous instanceof \PDOException) {
            $code = (string) $previous->getCode();

            return in_array($code, ['23503', '1451', '1452'], true);
        }

        return false;
    }
}
