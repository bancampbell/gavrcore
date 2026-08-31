<?php

namespace App\Modules\MenuManager\Domain\Repositories;

use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use Illuminate\Pagination\LengthAwarePaginator;

interface MenuItemRepositoryInterface
{
    public function getAll(int $menuTypeId, array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function getTree(int $menuTypeId): array;
    public function findById(int $id): ?MenuItemModel;
    public function findByIdWithLock(int $id): ?MenuItemModel;
    public function findByAlias(string $alias, int $menuTypeId): ?MenuItemModel;
    public function findByAliasWithLock(string $alias, int $menuTypeId): ?MenuItemModel;
    public function create(array $data): MenuItemModel;
    public function update(int $id, array $data): MenuItemModel;
    public function delete(int $id): bool;
    public function updateOrdering(array $order): bool;
    public function updateStatus(int $id, bool $status): bool;
    public function getChildren(int $parentId): array;
    public function getMaxOrdering(int $menuTypeId, ?int $parentId): int;
    public function incrementOrdering(int $menuTypeId, ?int $parentId, int $fromOrdering): void;
    public function decrementOrdering(int $menuTypeId, ?int $parentId, int $fromOrdering): void;
    public function lockByParent(int $menuTypeId, ?int $parentId): void;
    public function lockByMenuType(int $menuTypeId): void;
    public function isDescendant(int $ancestorId, int $descendantId): bool;
}
