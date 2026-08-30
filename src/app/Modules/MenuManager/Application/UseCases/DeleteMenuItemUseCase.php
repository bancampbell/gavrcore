<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;

class DeleteMenuItemUseCase
{
    public function __construct(private MenuItemRepositoryInterface $repository) {}

    public function execute(int $id): bool
    {
        $item = $this->repository->findById($id);
        if ($item) {
            $this->repository->decrementOrdering($item->menu_type_id, $item->parent_id, $item->ordering);
        }
        return $this->repository->delete($id);
    }
}