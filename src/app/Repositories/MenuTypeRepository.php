<?php

namespace App\Repositories;

use App\Contracts\MenuTypeRepositoryInterface;
use App\Models\MenuType;
use Illuminate\Pagination\LengthAwarePaginator;

class MenuTypeRepository implements MenuTypeRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MenuType::query()->withCount('items');

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('ordering')
            ->paginate($perPage);
    }

    public function findById(int $id): ?MenuType
    {
        return MenuType::withCount('items')->find($id);
    }

    public function findByAlias(string $alias): ?MenuType
    {
        return MenuType::where('alias', $alias)->first();
    }

    public function create(array $data): MenuType
    {
        return MenuType::create($data);
    }

    public function update(int $id, array $data): MenuType
    {
        $menuType = MenuType::findOrFail($id);
        $menuType->update($data);
        return $menuType->fresh();
    }

    public function delete(int $id): bool
    {
        return MenuType::destroy($id) > 0;
    }

    public function updateOrdering(array $order): bool
    {
        foreach ($order as $item) {
            MenuType::where('id', $item['id'])->update(['ordering' => $item['ordering']]);
        }

        return true;
    }
}
