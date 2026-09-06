<?php

namespace App\Modules\MenuManager\Infrastructure\Policies;

use App\Modules\UserManager\Infrastructure\Models\UserModel as User;

class MenuTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage menus');
    }

    public function view(User $user, $model = null): bool
    {
        return $user->can('manage menus');
    }

    public function create(User $user): bool
    {
        return $user->can('manage menus');
    }

    public function update(User $user, $model = null): bool
    {
        return $user->can('manage menus');
    }

    public function delete(User $user, $model = null): bool
    {
        return $user->can('manage menus');
    }
}
