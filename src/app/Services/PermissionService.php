<?php

namespace App\Services;

use App\Contracts\PermissionRepositoryInterface;
use App\DTO\PermissionData;
use App\Models\Permission;
use Illuminate\Support\Collection;

class PermissionService
{
    public function __construct(
        protected PermissionRepositoryInterface $repository
    ) {}

    /**
     * @return Collection<int, Permission>
     */
    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * @return Collection<string, Collection<int, Permission>>
     */
    public function getGrouped(): Collection
    {
        return $this->repository->getGrouped();
    }

    public function findById(int $id): ?Permission
    {
        return $this->repository->findById($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Permission
    {
        $permissionData = PermissionData::fromArray($data);
        return $this->repository->create($permissionData);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Permission
    {
        $permissionData = PermissionData::fromArray($data);
        return $this->repository->update($id, $permissionData);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
