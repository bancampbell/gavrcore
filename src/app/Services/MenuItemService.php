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

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, MenuItem>
     */
    public function getAll(int $menuTypeId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getAll($menuTypeId, $filters, $perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTree(int $menuTypeId): array
    {
        return $this->repository->getTree($menuTypeId);
    }

    public function findById(int $id): ?MenuItem
    {
        return $this->repository->findById($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(int $menuTypeId, array $data): MenuItem
    {
        if (empty($data['alias'])) {
            $data['alias'] = Str::slug($data['title']);
        }

        $data['menu_type_id'] = $menuTypeId;

        return $this->repository->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
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

    /**
     * @param array<int, array{id: int, ordering: int}> $order
     */
    public function updateOrdering(array $order): bool
    {
        return $this->repository->updateOrdering($order);
    }

    public function updateStatus(int $id, bool $status): bool
    {
        return $this->repository->updateStatus($id, $status);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getChildren(int $parentId): array
    {
        return $this->repository->getChildren($parentId);
    }
}
