<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasPermission('admin.access')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('users.manage') || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('users.manage') || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission('users.manage') && $user->id !== $model->id;
    }

    public function block(User $user, User $model): bool
    {
        return $user->hasPermission('users.manage') && $user->id !== $model->id;
    }

    public function bulkBlock(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function bulkUnblock(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }
}
