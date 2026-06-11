<?php

namespace App\Contracts;

use App\DTO\PermissionData;
use App\Models\Permission;
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

    public function findById(int $id): ?Permission;

    public function create(PermissionData $data): Permission;

    public function update(int $id, PermissionData $data): Permission;

    public function delete(int $id): bool;
}
