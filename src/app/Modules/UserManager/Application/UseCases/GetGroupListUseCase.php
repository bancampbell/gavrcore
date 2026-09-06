<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Application\DTO\GroupFiltersData;
use App\Modules\UserManager\Domain\Repositories\GroupRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class GetGroupListUseCase
{
    public function __construct(
        private readonly GroupRepositoryInterface $groups,
    ) {
    }

    public function execute(GroupFiltersData $filters): LengthAwarePaginator
    {
        return $this->groups->paginate($filters);
    }
}
