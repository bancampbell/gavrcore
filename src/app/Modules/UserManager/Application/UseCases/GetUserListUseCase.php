<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Application\DTO\UserFiltersData;
use App\Modules\UserManager\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class GetUserListUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function execute(UserFiltersData $filters): LengthAwarePaginator
    {
        return $this->users->paginate($filters);
    }
}
