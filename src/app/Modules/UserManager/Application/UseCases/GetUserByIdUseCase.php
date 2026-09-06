<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Domain\Entities\User;
use App\Modules\UserManager\Domain\Repositories\UserRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\UserId;

final class GetUserByIdUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function execute(UserId $id): ?User
    {
        return $this->users->findById($id);
    }
}
