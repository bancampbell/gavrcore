<?php

namespace App\Repositories;

use App\Contracts\GroupRepositoryInterface;
use App\Models\Group;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GroupRepository implements GroupRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Group::query();

        if (! empty($filters['search'])) {
            $query->where('name', 'ilike', '%'.$filters['search'].'%');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('ordering')->paginate($perPage);
    }

    public function find(int $id): ?Group
    {
        return Group::find($id);
    }

    public function create(array $data): Group
    {
        return Group::create($data);
    }

    public function update(int $id, array $data): Group
    {
        $group = Group::findOrFail($id);
        $group->update($data);

        return $group->fresh();
    }

    public function delete(int $id): bool
    {
        return Group::destroy($id) > 0;
    }

    public function updateStatus(int $id, bool $status): bool
    {
        return Group::where('id', $id)->update(['status' => $status]) > 0;
    }

    public function updateOrdering(array $order): bool
    {
        foreach ($order as $item) {
            Group::where('id', $item['id'])->update(['ordering' => $item['ordering']]);
        }

        return true;
    }
}
