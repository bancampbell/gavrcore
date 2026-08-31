<?php

namespace App\Modules\MenuManager\Infrastructure\Repositories;

use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MenuItemRepository implements MenuItemRepositoryInterface
{
    public function getAll(int $menuTypeId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MenuItemModel::where('menu_type_id', $menuTypeId);
        if (! empty($filters['search'])) $query->where('title', 'like', '%'.$filters['search'].'%');
        if (isset($filters['status'])) $query->where('status', $filters['status']);
        return $query->orderBy('ordering')->paginate($perPage);
    }

    public function getTree(int $menuTypeId): array
    {
        $items = MenuItemModel::where('menu_type_id', $menuTypeId)->orderBy('ordering')->get();
        return $this->buildTree($items->toArray());
    }

    public function findById(int $id): ?MenuItemModel
    {
        return MenuItemModel::find($id);
    }

    public function findByIdWithLock(int $id): ?MenuItemModel
    {
        return MenuItemModel::where('id', $id)->lockForUpdate()->first();
    }

    public function findByAlias(string $alias, int $menuTypeId): ?MenuItemModel
    {
        return MenuItemModel::where('alias', $alias)->where('menu_type_id', $menuTypeId)->first();
    }

    public function findByAliasWithLock(string $alias, int $menuTypeId): ?MenuItemModel
    {
        return MenuItemModel::where('alias', $alias)->where('menu_type_id', $menuTypeId)->lockForUpdate()->first();
    }

    public function create(array $data): MenuItemModel
    {
        return MenuItemModel::create($data);
    }

    public function update(int $id, array $data): MenuItemModel
    {
        $item = MenuItemModel::findOrFail($id);
        $item->update($data);
        return $item->fresh();
    }

    public function delete(int $id): bool
    {
        return MenuItemModel::destroy($id) > 0;
    }

    public function updateOrdering(array $order): bool
    {
        return DB::transaction(function () use ($order) {
            $ids = array_column($order, 'id');
            if (! empty($ids)) {
                MenuItemModel::whereIn('id', $ids)->lockForUpdate()->get();
            }

            foreach ($order as $item) {
                MenuItemModel::where('id', $item['id'])->update(['ordering' => $item['ordering']]);
            }
            return true;
        });
    }

    public function updateStatus(int $id, bool $status): bool
    {
        return MenuItemModel::where('id', $id)->update(['status' => $status]) > 0;
    }

    public function getChildren(int $parentId): array
    {
        return MenuItemModel::where('parent_id', $parentId)->orderBy('ordering')->get()->toArray();
    }

    public function getMaxOrdering(int $menuTypeId, ?int $parentId): int
    {
        $query = MenuItemModel::where('menu_type_id', $menuTypeId);
        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }
        $max = $query->max('ordering');

        return $max ?? 0;
    }

    public function incrementOrdering(int $menuTypeId, ?int $parentId, int $fromOrdering): void
    {
        $query = MenuItemModel::where('menu_type_id', $menuTypeId)
            ->where('ordering', '>=', $fromOrdering);
        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }
        $query->increment('ordering');
    }

    public function decrementOrdering(int $menuTypeId, ?int $parentId, int $fromOrdering): void
    {
        $query = MenuItemModel::where('menu_type_id', $menuTypeId)
            ->where('ordering', '>', $fromOrdering);
        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }
        $query->decrement('ordering');
    }

    public function lockByParent(int $menuTypeId, ?int $parentId): void
    {
        $query = MenuItemModel::where('menu_type_id', $menuTypeId);
        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }
        $query->lockForUpdate()->get();
    }

    public function lockByMenuType(int $menuTypeId): void
    {
        MenuItemModel::where('menu_type_id', $menuTypeId)
            ->lockForUpdate()
            ->get();
    }

    public function isDescendant(int $ancestorId, int $descendantId): bool
    {
        if ($ancestorId === $descendantId) {
            return true;
        }

        $current = $descendantId;
        $visited = [];

        while ($current !== null) {
            if (in_array($current, $visited, true)) {
                break;
            }
            $visited[] = $current;

            $parent = MenuItemModel::where('id', $current)->value('parent_id');

            if ($parent === null) {
                break;
            }

            if ($parent === $ancestorId) {
                return true;
            }

            $current = $parent;
        }

        return false;
    }

    private function buildTree(array $items, ?int $parentId = null): array
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
