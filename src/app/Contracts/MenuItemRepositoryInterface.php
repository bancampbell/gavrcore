<?php

namespace App\Contracts;

use App\Models\MenuItem;
use Illuminate\Pagination\LengthAwarePaginator;

interface MenuItemRepositoryInterface
{
    public function getAll(int $menuTypeId, array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function getTree(int $menuTypeId): array;
    public function findById(int $id): ?MenuItem;
    public function findByAlias(string $alias, int $menuTypeId): ?MenuItem;
    public function create(array $data): MenuItem;
    public function update(int $id, array $data): MenuItem;
    public function delete(int $id): bool;
    public function updateOrdering(array $order): bool;
    public function updateStatus(int $id, bool $status): bool;
    public function getChildren(int $parentId): array;
}
