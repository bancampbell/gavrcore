<?php

namespace App\Modules\UserManager\Infrastructure\Policies;

use App\Modules\UserManager\Domain\Entities\AccessLevel;
use App\Modules\UserManager\Infrastructure\Models\UserModel;

class AccessLevelPolicy
{
    public function before(UserModel $user, string $ability): ?bool
    {
        if ($user->hasPermission('admin.access')) {
            return true;
        }

        return null;
    }

    public function viewAny(UserModel $user): bool
    {
        return $user->hasPermission('permissions.manage');
    }

    public function view(UserModel $user, AccessLevel $accessLevel): bool
    {
        return $user->hasPermission('permissions.manage');
    }

    public function create(UserModel $user): bool
    {
        return $user->hasPermission('permissions.manage');
    }

    public function updateOrdering(UserModel $user): bool
    {
        return $user->hasPermission('permissions.manage');
    }

    public function update(UserModel $user, AccessLevel $accessLevel): bool
    {
        return $user->hasPermission('permissions.manage');
    }

    public function delete(UserModel $user, AccessLevel $accessLevel): bool
    {
        return $user->hasPermission('permissions.manage');
    }
}
