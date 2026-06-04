<?php

namespace App\Repositories;

use App\Contracts\MenuItemRepositoryInterface;
use App\Models\MenuItem;
use Illuminate\Pagination\LengthAwarePaginator;

class MenuItemRepository implements MenuItemRepositoryInterface
{
    public function getAll(int $menuTypeId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MenuItem::where('menu_type_id', $menuTypeId);

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('ordering')
            ->paginate($perPage);
    }

    public function getTree(int $menuTypeId): array
    {
        $items = MenuItem::where('menu_type_id', $menuTypeId)
            ->orderBy('ordering')
            ->get();

        return $this->buildTree($items->toArray());
    }

    public function findById(int $id): ?MenuItem
    {
        return MenuItem::find($id);
    }

    public function findByAlias(string $alias, int $menuTypeId): ?MenuItem
    {
        return MenuItem::where('alias', $alias)
            ->where('menu_type_id', $menuTypeId)
            ->first();
    }

    public function create(array $data): MenuItem
    {
        return MenuItem::create($data);
    }

    public function update(int $id, array $data): MenuItem
    {
        $item = MenuItem::findOrFail($id);
        $item->update($data);
        return $item->fresh();
    }

    public function delete(int $id): bool
    {
        return MenuItem::destroy($id) > 0;
    }

    public function updateOrdering(array $order): bool
    {
        foreach ($order as $item) {
            MenuItem::where('id', $item['id'])->update(['ordering' => $item['ordering']]);
        }

        return true;
    }

    public function updateStatus(int $id, bool $status): bool
    {
        return MenuItem::where('id', $id)->update(['status' => $status]) > 0;
    }

    public function getChildren(int $parentId): array
    {
        $children = MenuItem::where('parent_id', $parentId)
            ->orderBy('ordering')
            ->get();

        return $children->toArray();
    }

    private function buildTree(array $items, int $parentId = null): array
    {
        $tree = [];

        foreach ($items as $item) {
            if ($item['parent_id'] === $parentId) {
                $children = $this->buildTree($items, $item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }

        return $tree;
    }
}
