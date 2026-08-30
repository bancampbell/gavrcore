<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Application\DTO\MenuTypeFiltersData;
use App\Modules\MenuManager\Domain\Repositories\MenuTypeRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class GetMenuTypeListUseCase
{
    public function __construct(private MenuTypeRepositoryInterface $repository) {}

    public function execute(MenuTypeFiltersData $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getAll([
            'search' => $filters->search,
            'status' => $filters->status,
        ], $perPage);
    }
}