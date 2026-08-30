<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;

class GetMenuItemTreeUseCase
{
    public function __construct(private MenuItemRepositoryInterface $repository) {}

    public function execute(int $menuTypeId): array
    {
        return $this->repository->getTree($menuTypeId);
    }
}