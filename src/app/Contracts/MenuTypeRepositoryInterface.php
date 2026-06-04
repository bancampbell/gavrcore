<?php

namespace App\Contracts;

use App\Models\MenuType;
use Illuminate\Pagination\LengthAwarePaginator;

interface MenuTypeRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function findById(int $id): ?MenuType;
    public function findByAlias(string $alias): ?MenuType;
    public function create(array $data): MenuType;
    public function update(int $id, array $data): MenuType;
    public function delete(int $id): bool;
    public function updateOrdering(array $order): bool;
}
