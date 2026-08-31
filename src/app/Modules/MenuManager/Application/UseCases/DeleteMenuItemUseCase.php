<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteMenuItemUseCase
{
    public function __construct(private MenuItemRepositoryInterface $repository) {}

    public function execute(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $item = $this->repository->findById($id);
            if (! $item) {
                return false;
            }

            // Coarse-first: блокируем весь тип меню, затем строку, затем scope родителя.
            $this->repository->lockByMenuType($item->menu_type_id);

            $item = $this->repository->findByIdWithLock($id);
            if (! $item) {
                return false;
            }

            $this->repository->lockByParent($item->menu_type_id, $item->parent_id);
            $this->repository->decrementOrdering($item->menu_type_id, $item->parent_id, $item->ordering);
            return $this->repository->delete($id);
        });
    }
}
