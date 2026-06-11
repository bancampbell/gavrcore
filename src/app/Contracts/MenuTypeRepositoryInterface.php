<?php

namespace App\Contracts;

use App\Models\MenuType;
use Illuminate\Pagination\LengthAwarePaginator;

interface MenuTypeRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     *
     * @return LengthAwarePaginator<int, MenuType>
     */
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function findById(int $id): ?MenuType;

    public function findByAlias(string $alias): ?MenuType;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): MenuType;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): MenuType;

    public function delete(int $id): bool;

    /**
     * @param  array<int, array{id: int, ordering: int}>  $order
     */
    public function updateOrdering(array $order): bool;
}
