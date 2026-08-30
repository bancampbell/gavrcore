<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Application\DTO\MenuItemFiltersData;
use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class GetMenuItemListUseCase
{
    public function __construct(private MenuItemRepositoryInterface $repository) {}

    public function execute(int $menuTypeId, MenuItemFiltersData $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getAll($menuTypeId, [
            'search' => $filters->search,
            'status' => $filters->status,
        ], $perPage);
    }
}