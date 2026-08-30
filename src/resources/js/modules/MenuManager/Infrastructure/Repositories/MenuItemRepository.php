<?php

namespace App\Modules\MenuManager\Infrastructure\Repositories;

use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function findByAlias(string $alias, int $menuTypeId): ?MenuItemModel
    {
        return MenuItemModel::where('alias', $alias)->where('menu_type_id', $menuTypeId)->first();
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
        foreach ($order as $item) {
            MenuItemModel::where('id', $item['id'])->update(['ordering' => $item['ordering']]);
        }
        return true;
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
        $max = MenuItemModel::where('menu_type_id', $menuTypeId)->where('parent_id', $parentId)->max('ordering');
        return $max ?? 0;
    }

    public function incrementOrdering(int $menuTypeId, ?int $parentId, int $fromOrdering): void
    {
        MenuItemModel::where('menu_type_id', $menuTypeId)->where('parent_id', $parentId)->where('ordering', '>=', $fromOrdering)->increment('ordering');
    }

    public function decrementOrdering(int $menuTypeId, ?int $parentId, int $fromOrdering): void
    {
        MenuItemModel::where('menu_type_id', $menuTypeId)->where('parent_id', $parentId)->where('ordering', '>', $fromOrdering)->decrement('ordering');
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