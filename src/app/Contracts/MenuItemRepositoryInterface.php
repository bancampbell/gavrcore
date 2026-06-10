<?php

namespace App\Contracts;

use App\Models\MenuItem;
use Illuminate\Pagination\LengthAwarePaginator;

interface MenuItemRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, MenuItem>
     */
    public function getAll(int $menuTypeId, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTree(int $menuTypeId): array;

    public function findById(int $id): ?MenuItem;
    public function findByAlias(string $alias, int $menuTypeId): ?MenuItem;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): MenuItem;

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): MenuItem;

    public function delete(int $id): bool;

    /**
     * @param array<int, array{id: int, ordering: int}> $order
     */
    public function updateOrdering(array $order): bool;

    public function updateStatus(int $id, bool $status): bool;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getChildren(int $parentId): array;
}
