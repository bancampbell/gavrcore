<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Application\DTO\UpdateUserData;
use App\Modules\UserManager\Domain\Entities\User;
use App\Modules\UserManager\Domain\Events\UserUpdated;
use App\Modules\UserManager\Domain\Repositories\UserRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\UserId;
use App\Modules\UserManager\Infrastructure\Models\UserModel;

final class UpdateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function execute(UserId $id, UpdateUserData $data): User
    {
        $user = $this->users->update($id, $data);

        // Одиночное редактирование тоже может заблокировать/деактивировать —
        // токены отзываем наравне с массовой операцией.
        if ($user->blocked || ! $user->activated) {
            UserModel::whereKey($id->value)->first()?->tokens()->delete();
        }

        event(new UserUpdated($user));

        return $user;
    }
}
