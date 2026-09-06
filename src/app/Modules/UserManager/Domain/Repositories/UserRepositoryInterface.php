<?php

namespace App\Modules\UserManager\Domain\Repositories;

use App\Modules\UserManager\Application\DTO\CreateUserData;
use App\Modules\UserManager\Application\DTO\UpdateUserData;
use App\Modules\UserManager\Application\DTO\UserFiltersData;
use App\Modules\UserManager\Domain\Entities\User;
use App\Modules\UserManager\Domain\ValueObjects\UserId;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(UserFiltersData $filters): LengthAwarePaginator;

    public function findById(UserId $id): ?User;

    public function create(CreateUserData $data): User;

    public function update(UserId $id, UpdateUserData $data): User;

    public function delete(UserId $id): void;

    public function updateStatus(UserId $id, bool $blocked): bool;
}
