<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Domain\Events\UserDeleted;
use App\Modules\UserManager\Domain\Repositories\UserRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\UserId;
use DomainException;

final class DeleteUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function execute(UserId $id): void
    {
        $user = $this->users->findById($id);

        if (! $user) {
            throw new DomainException('Пользователь не найден');
        }

        $this->users->delete($id);

        event(new UserDeleted($user));
    }
}
