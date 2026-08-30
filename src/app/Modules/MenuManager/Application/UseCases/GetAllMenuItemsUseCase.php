<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Application\DTO\MenuItemFiltersData;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use Illuminate\Pagination\LengthAwarePaginator;

class GetAllMenuItemsUseCase
{
    public function execute(MenuItemFiltersData $filters, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $query = MenuItemModel::with('menuType')->orderBy('created_at', 'desc');
        if (! empty($filters->search)) $query->where('title', 'like', '%'.$filters->search.'%');
        if ($filters->status !== null) $query->where('status', $filters->status);
        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}