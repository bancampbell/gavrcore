<?php

namespace App\Services;

use App\Contracts\GroupRepositoryInterface;
use App\Models\Group;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class GroupService
{
    public function __construct(
        protected GroupRepositoryInterface $repository
    ) {}

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Group>
     */
    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function find(int $id): ?Group
    {
        return $this->repository->find($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Group
    {
        if (empty($data['alias'])) {
            $data['alias'] = Str::slug($data['name']);
        }
        return $this->repository->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Group
    {
        if (isset($data['alias']) && empty($data['alias'])) {
            unset($data['alias']);
        }
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function updateStatus(int $id, bool $status): bool
    {
        return $this->repository->updateStatus($id, $status);
    }

    /**
     * @param array<int, array{id: int, ordering: int}> $order
     */
    public function updateOrdering(array $order): bool
    {
        return $this->repository->updateOrdering($order);
    }
}
