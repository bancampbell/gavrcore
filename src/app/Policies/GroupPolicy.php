<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Group;

class GroupPolicy
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
        return $user->hasPermission('groups.manage');
    }

    public function view(User $user, Group $group): bool
    {
        return $user->hasPermission('groups.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('groups.manage');
    }

    public function update(User $user, Group $group): bool
    {
        return $user->hasPermission('groups.manage');
    }

    public function delete(User $user, Group $group): bool
    {
        return $user->hasPermission('groups.manage');
    }

    public function publish(User $user): bool
    {
        return $user->hasPermission('groups.manage');
    }

    public function unpublish(User $user): bool
    {
        return $user->hasPermission('groups.manage');
    }
}
