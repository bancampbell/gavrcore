<?php

namespace App\Services;

use App\Contracts\MenuItemRepositoryInterface;
use App\Models\MenuItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class MenuItemService
{
    protected MenuItemRepositoryInterface $repository;

    public function __construct(MenuItemRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(int $menuTypeId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getAll($menuTypeId, $filters, $perPage);
    }

    public function getTree(int $menuTypeId): array
    {
        return $this->repository->getTree($menuTypeId);
    }

    public function findById(int $id): ?MenuItem
    {
        return $this->repository->findById($id);
    }

    public function create(int $menuTypeId, array $data): MenuItem
    {
        if (empty($data['alias'])) {
            $data['alias'] = Str::slug($data['title']);
        }

        $data['menu_type_id'] = $menuTypeId;

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): MenuItem
    {
        if (empty($data['alias'])) {
            $data['alias'] = Str::slug($data['title']);
        }

        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function updateOrdering(array $order): bool
    {
        return $this->repository->updateOrdering($order);
    }

    public function updateStatus(int $id, bool $status): bool
    {
        return $this->repository->updateStatus($id, $status);
    }

    public function getChildren(int $parentId): array
    {
        return $this->repository->getChildren($parentId);
    }
}
