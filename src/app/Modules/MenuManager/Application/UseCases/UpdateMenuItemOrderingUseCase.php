<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;

class UpdateMenuItemOrderingUseCase
{
    public function __construct(private MenuItemRepositoryInterface $repository) {}

    public function execute(array $order): bool
    {
        return $this->repository->updateOrdering($order);
    }
}