<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;

class UpdateMenuItemStatusUseCase
{
    public function __construct(private MenuItemRepositoryInterface $repository) {}

    public function execute(int $id, bool $status): bool
    {
        return $this->repository->updateStatus($id, $status);
    }
}