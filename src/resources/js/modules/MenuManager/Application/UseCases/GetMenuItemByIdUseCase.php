<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;

class GetMenuItemByIdUseCase
{
    public function __construct(private MenuItemRepositoryInterface $repository) {}

    public function execute(int $id): ?MenuItemModel
    {
        return $this->repository->findById($id);
    }
}