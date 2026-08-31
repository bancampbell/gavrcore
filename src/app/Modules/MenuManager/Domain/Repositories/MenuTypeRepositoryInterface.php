<?php

namespace App\Modules\MenuManager\Domain\Repositories;

use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;
use Illuminate\Pagination\LengthAwarePaginator;

interface MenuTypeRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function findById(int $id): ?MenuTypeModel;
    public function findByAlias(string $alias): ?MenuTypeModel;
    public function findByAliasWithLock(string $alias): ?MenuTypeModel;
    public function create(array $data): MenuTypeModel;
    public function update(int $id, array $data): MenuTypeModel;
    public function delete(int $id): bool;
    public function updateOrdering(array $order): bool;
}
