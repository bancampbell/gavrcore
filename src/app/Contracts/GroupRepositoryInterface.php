<?php

namespace App\Contracts;

use App\Models\Group;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GroupRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function find(int $id): ?Group;
    public function create(array $data): Group;
    public function update(int $id, array $data): Group;
    public function delete(int $id): bool;
    public function updateStatus(int $id, bool $status): bool;
    public function updateOrdering(array $order): bool;
}
