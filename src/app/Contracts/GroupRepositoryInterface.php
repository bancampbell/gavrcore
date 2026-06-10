<?php

namespace App\Contracts;

use App\Models\Group;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GroupRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Group>
     */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function find(int $id): ?Group;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Group;

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Group;

    public function delete(int $id): bool;
    public function updateStatus(int $id, bool $status): bool;

    /**
     * @param array<int, array{id: int, ordering: int}> $order
     */
    public function updateOrdering(array $order): bool;
}
