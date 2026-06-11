<?php

namespace App\Policies;

use App\Models\AccessLevel;
use App\Models\User;

class AccessLevelPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasPermission('admin.access')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('permissions.manage');
    }

    public function view(User $user, AccessLevel $accessLevel): bool
    {
        return $user->hasPermission('permissions.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('permissions.manage');
    }

    public function update(User $user, AccessLevel $accessLevel): bool
    {
        return $user->hasPermission('permissions.manage');
    }

    public function delete(User $user, AccessLevel $accessLevel): bool
    {
        return $user->hasPermission('permissions.manage');
    }
}
