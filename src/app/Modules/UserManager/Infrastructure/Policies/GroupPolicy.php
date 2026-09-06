<?php

namespace App\Modules\UserManager\Infrastructure\Policies;

use App\Modules\UserManager\Domain\Entities\Group;
use App\Modules\UserManager\Infrastructure\Models\UserModel;

class GroupPolicy
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
        return $user->hasPermission('groups.manage');
    }

    public function view(UserModel $user, Group $group): bool
    {
        return $user->hasPermission('groups.manage');
    }

    public function create(UserModel $user): bool
    {
        return $user->hasPermission('groups.manage');
    }

    public function update(UserModel $user, Group $group): bool
    {
        return $user->hasPermission('groups.manage');
    }

    public function delete(UserModel $user, Group $group): bool
    {
        return $user->hasPermission('groups.manage');
    }

    public function publish(UserModel $user): bool
    {
        return $user->hasPermission('groups.manage');
    }

    public function unpublish(UserModel $user): bool
    {
        return $user->hasPermission('groups.manage');
    }
}
