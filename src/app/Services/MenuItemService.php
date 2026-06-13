<?php

namespace App\Services;

use App\Contracts\MenuItemRepositoryInterface;
use App\Models\MenuItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class MenuItemService
{
    protected MenuItemRepositoryInterface $repository;

    public function __construct(MenuItemRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  array<string, mixed>  $filters
     *
     * @return LengthAwarePaginator<int, MenuItem>
     */
    public function getAll(int $menuTypeId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getAll($menuTypeId, $filters, $perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTree(int $menuTypeId): array
    {
        return $this->repository->getTree($menuTypeId);
    }

    public function findById(int $id): ?MenuItem
    {
        return $this->repository->findById($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $menuTypeId, array $data): MenuItem
    {
        if (empty($data['alias'])) {
            $data['alias'] = Str::slug($data['title']);
        }

        $data['menu_type_id'] = $menuTypeId;

        // Получаем position и after_id из request (через request() helper)
        $position = request()->input('position');
        $afterId = request()->input('after_id');

        // Получаем максимальный ordering для текущего меню и родителя
        $maxOrdering = $this->repository->getMaxOrdering($menuTypeId, $data['parent_id'] ?? null);

        if ($position === 'first') {
            // В начало списка - сдвигаем все элементы вправо
            $this->repository->incrementOrdering($menuTypeId, $data['parent_id'] ?? null, 0);
            $data['ordering'] = 0;
        } elseif ($afterId) {
            // После указанного элемента
            $afterItem = $this->repository->findById($afterId);
            if ($afterItem && $afterItem->menu_type_id === $menuTypeId) {
                $newOrdering = $afterItem->ordering + 1;
                $this->repository->incrementOrdering($menuTypeId, $data['parent_id'] ?? null, $newOrdering);
                $data['ordering'] = $newOrdering;
            } else {
                $data['ordering'] = $maxOrdering + 1;
            }
        } else {
            // В конец списка (по умолчанию)
            $data['ordering'] = $maxOrdering + 1;
        }

        return $this->repository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): MenuItem
    {
        if (empty($data['alias'])) {
            $data['alias'] = Str::slug($data['title']);
        }

        $item = $this->repository->findById($id);
        if (!$item) {
            throw new \Exception('Menu item not found');
        }

        // Получаем position и after_id из request
        $position = request()->input('position');
        $afterId = request()->input('after_id');

        $oldParentId = $item->parent_id;
        $newParentId = $data['parent_id'] ?? $oldParentId;
        $oldOrdering = $item->ordering;

        // Если нужно переместить элемент
        if ($position === 'first' || $afterId) {
            // Удаляем элемент из текущей позиции (сдвигаем остальные влево)
            $this->repository->decrementOrdering($item->menu_type_id, $oldParentId, $oldOrdering);

            if ($position === 'first') {
                // В начало списка
                $this->repository->incrementOrdering($item->menu_type_id, $newParentId, 0);
                $data['ordering'] = 0;
            } elseif ($afterId) {
                // После указанного элемента
                $afterItem = $this->repository->findById($afterId);
                if ($afterItem && $afterItem->menu_type_id === $item->menu_type_id) {
                    $newOrdering = $afterItem->ordering + 1;
                    $this->repository->incrementOrdering($item->menu_type_id, $newParentId, $newOrdering);
                    $data['ordering'] = $newOrdering;
                } else {
                    $maxOrdering = $this->repository->getMaxOrdering($item->menu_type_id, $newParentId);
                    $data['ordering'] = $maxOrdering + 1;
                }
            }
        } elseif ($oldParentId != $newParentId) {
            // Только сменился родитель, без указания позиции - в конец нового родителя
            $this->repository->decrementOrdering($item->menu_type_id, $oldParentId, $oldOrdering);
            $maxOrdering = $this->repository->getMaxOrdering($item->menu_type_id, $newParentId);
            $data['ordering'] = $maxOrdering + 1;
        }

        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $item = $this->repository->findById($id);
        if ($item) {
            // Сдвигаем элементы, идущие после удаляемого
            $this->repository->decrementOrdering($item->menu_type_id, $item->parent_id, $item->ordering);
        }

        return $this->repository->delete($id);
    }

    /**
     * @param  array<int, array{id: int, ordering: int}>  $order
     */
    public function updateOrdering(array $order): bool
    {
        return $this->repository->updateOrdering($order);
    }

    public function updateStatus(int $id, bool $status): bool
    {
        return $this->repository->updateStatus($id, $status);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getChildren(int $parentId): array
    {
        return $this->repository->getChildren($parentId);
    }
}
