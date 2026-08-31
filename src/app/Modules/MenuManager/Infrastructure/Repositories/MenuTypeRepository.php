<?php

namespace App\Modules\MenuManager\Infrastructure\Repositories;

use App\Modules\MenuManager\Domain\Repositories\MenuTypeRepositoryInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MenuTypeRepository implements MenuTypeRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MenuTypeModel::query()->withCount('items');
        if (! empty($filters['search'])) $query->where('title', 'like', '%'.$filters['search'].'%');
        if (isset($filters['status'])) $query->where('status', $filters['status']);
        return $query->orderBy('ordering')->paginate($perPage);
    }

    public function findById(int $id): ?MenuTypeModel
    {
        return MenuTypeModel::withCount('items')->find($id);
    }

    public function findByAlias(string $alias): ?MenuTypeModel
    {
        return MenuTypeModel::where('alias', $alias)->first();
    }

    public function findByAliasWithLock(string $alias): ?MenuTypeModel
    {
        return MenuTypeModel::where('alias', $alias)->lockForUpdate()->first();
    }

    public function create(array $data): MenuTypeModel
    {
        return MenuTypeModel::create($data);
    }

    public function update(int $id, array $data): MenuTypeModel
    {
        $menuType = MenuTypeModel::findOrFail($id);
        $menuType->update($data);
        return $menuType->fresh();
    }

    public function delete(int $id): bool
    {
        return MenuTypeModel::destroy($id) > 0;
    }

    public function updateOrdering(array $order): bool
    {
        return DB::transaction(function () use ($order) {
            $ids = array_column($order, 'id');
            if (! empty($ids)) {
                MenuTypeModel::whereIn('id', $ids)->lockForUpdate()->get();
            }

            foreach ($order as $item) {
                MenuTypeModel::where('id', $item['id'])->update(['ordering' => $item['ordering']]);
            }
            return true;
        });
    }
}
