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

    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    public function findById(int $id): ?MenuType
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): MenuType
    {
        if (empty($data['alias'])) {
            $data['alias'] = Str::slug($data['title']);
        }

        return $this->repository->create($data);
    }

    public function update(int $id, array $data): MenuType
    {
        // Получаем существующий тип меню
        $existingMenuType = MenuType::findOrFail($id);

        // Если alias не передан, оставляем старый
        if (!isset($data['alias']) || empty($data['alias'])) {
            $data['alias'] = $existingMenuType->alias;
        }

        // Если alias передан, но пустой, генерируем из заголовка
        if (isset($data['alias']) && empty($data['alias'])) {
            $data['alias'] = Str::slug($data['title'] ?? $existingMenuType->title);
        }

        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function updateOrdering(array $order): bool
    {
        return $this->repository->updateOrdering($order);
    }
}
