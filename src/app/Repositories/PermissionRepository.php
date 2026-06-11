<?php

namespace App\Repositories;

use App\Contracts\PermissionRepositoryInterface;
use App\DTO\PermissionData;
use App\Models\Permission;
use Illuminate\Support\Collection;

class PermissionRepository implements PermissionRepositoryInterface
{
    /**
     * @return Collection<int, Permission>
     */
    public function getAll(): Collection
    {
        return Permission::orderBy('group')->orderBy('name')->get();
    }

    /**
     * @return Collection<string, Collection<int, Permission>>
     */
    public function getGrouped(): Collection
    {
        $grouped = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');

        $result = new Collection();
        foreach ($grouped as $group => $permissions) {
            $result->put((string) $group, $permissions->values());
        }

        return $result;
    }

    public function findById(int $id): ?Permission
    {
        return Permission::find($id);
    }

    public function create(PermissionData $data): Permission
    {
        return Permission::create($data->toArray());
    }

    public function update(int $id, PermissionData $data): Permission
    {
        $permission = Permission::findOrFail($id);
        $permission->update($data->toArray());

        return $permission->fresh();
    }

    public function delete(int $id): bool
    {
        return Permission::destroy($id) > 0;
    }
}
