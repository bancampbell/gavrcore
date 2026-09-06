<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Application\DTO\CreateUserData;
use App\Modules\UserManager\Domain\Entities\User;
use App\Modules\UserManager\Domain\Events\UserCreated;
use App\Modules\UserManager\Domain\Repositories\UserRepositoryInterface;

final class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function execute(CreateUserData $data): User
    {
        $user = $this->users->create($data);

        event(new UserCreated($user));

        return $user;
    }
}
