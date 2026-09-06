<?php

namespace App\Modules\UserManager\Domain\Repositories;

use App\Modules\UserManager\Application\DTO\CreatePermissionData;
use App\Modules\UserManager\Application\DTO\UpdatePermissionData;
use App\Modules\UserManager\Domain\Entities\Permission;
use App\Modules\UserManager\Domain\ValueObjects\PermissionId;
use Illuminate\Support\Collection;

interface PermissionRepositoryInterface
{
    /**
     * @return Collection<int, Permission>
     */
    public function getAll(): Collection;

    /**
     * @return Collection<string, Collection<int, Permission>>
     */
    public function getGrouped(): Collection;

    public function findById(PermissionId $id): ?Permission;

    public function create(CreatePermissionData $data): Permission;

    public function update(PermissionId $id, UpdatePermissionData $data): Permission;

    public function delete(PermissionId $id): bool;
}
