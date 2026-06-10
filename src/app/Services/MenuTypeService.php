<?php

namespace App\Services;

use App\Contracts\MenuTypeRepositoryInterface;
use App\Models\MenuType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class MenuTypeService
{
    protected MenuTypeRepositoryInterface $repository;

    public function __construct(MenuTypeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, MenuType>
     */
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    public function findById(int $id): ?MenuType
    {
        return $this->repository->findById($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): MenuType
    {
        if (empty($data['alias'])) {
            $data['alias'] = Str::slug($data['title']);
        }

        return $this->repository->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): MenuType
    {
        $existingMenuType = MenuType::findOrFail($id);

        if (empty($data['alias'])) {
            $data['alias'] = $existingMenuType->alias;
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
}
