<?php

namespace App\Modules\UserManager\Infrastructure\Policies;

use App\Modules\UserManager\Domain\Entities\User;
use App\Modules\UserManager\Infrastructure\Models\UserModel;

class UserPolicy
{
    public function before(UserModel $user, string $ability, ...$args): ?bool
    {
        $target = $args[0] ?? null;

        // Самоудаление/самоблокировка запрещены даже для admin.access:
        // иначе последний админ может вывести себя из строя безвозвратно.
        if (in_array($ability, ['delete', 'block'], true)
            && $target instanceof User
            && $target->id?->value === $user->id) {
            return false;
        }

        if ($user->hasPermission('admin.access')) {
            return true;
        }

        return null;
    }

    public function viewAny(UserModel $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function view(UserModel $user, User $model): bool
    {
        return $user->hasPermission('users.manage') || $user->id === $model->id?->value;
    }

    public function create(UserModel $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function update(UserModel $user, User $model): bool
    {
        return $user->hasPermission('users.manage') || $user->id === $model->id?->value;
    }

    public function delete(UserModel $user, User $model): bool
    {
        return $user->hasPermission('users.manage') && $user->id !== $model->id?->value;
    }

    public function block(UserModel $user, User $model): bool
    {
        return $user->hasPermission('users.manage') && $user->id !== $model->id?->value;
    }

    public function bulkBlock(UserModel $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function bulkUnblock(UserModel $user): bool
    {
        return $user->hasPermission('users.manage');
    }
}
