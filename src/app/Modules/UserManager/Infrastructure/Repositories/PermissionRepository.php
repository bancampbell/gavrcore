<?php

namespace App\Modules\UserManager\Infrastructure\Repositories;

use App\Modules\UserManager\Application\DTO\CreatePermissionData;
use App\Modules\UserManager\Application\DTO\UpdatePermissionData;
use App\Modules\UserManager\Domain\Entities\Permission;
use App\Modules\UserManager\Domain\Repositories\PermissionRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\PermissionId;
use App\Modules\UserManager\Infrastructure\Models\PermissionModel;
use Illuminate\Support\Collection;

final class PermissionRepository implements PermissionRepositoryInterface
{
    public function getAll(): Collection
    {
        return PermissionModel::orderBy('group')->orderBy('name')
            ->get()
            ->map(fn (PermissionModel $model) => $this->toEntity($model));
    }

    public function getGrouped(): Collection
    {
        $grouped = $this->getAll()->groupBy(
            fn (Permission $permission) => $permission->group ?? 'Другие'
        );

        $result = new Collection();
        foreach ($grouped as $group => $permissions) {
            $result->put((string) $group, $permissions->values());
        }

        return $result;
    }

    public function findById(PermissionId $id): ?Permission
    {
        $model = PermissionModel::find($id->value);

        return $model ? $this->toEntity($model) : null;
    }

    public function create(CreatePermissionData $data): Permission
    {
        /** @var PermissionModel $model */
        $model = PermissionModel::create($data->toArray());

        return $this->toEntity($model);
    }

    public function update(PermissionId $id, UpdatePermissionData $data): Permission
    {
        $model = PermissionModel::findOrFail($id->value);
        $model->update($data->toArray());

        return $this->toEntity($model->fresh());
    }

    public function delete(PermissionId $id): bool
    {
        return PermissionModel::destroy($id->value) > 0;
    }

    private function toEntity(PermissionModel $model): Permission
    {
        return new Permission(
            id: PermissionId::fromInt($model->id),
            name: $model->name,
            key: $model->key,
            group: $model->group,
            description: $model->description,
        );
    }
}
