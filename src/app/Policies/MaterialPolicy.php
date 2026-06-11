<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\User;

class MaterialPolicy
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
        return $user->hasPermission('materials.manage');
    }

    public function view(User $user, Material $material): bool
    {
        return $user->hasPermission('materials.manage') || $user->id === $material->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('materials.manage');
    }

    public function update(User $user, Material $material): bool
    {
        return $user->hasPermission('materials.manage') || $user->id === $material->user_id;
    }

    public function delete(User $user, Material $material): bool
    {
        return $user->hasPermission('materials.manage');
    }

    public function publish(User $user): bool
    {
        return $user->hasPermission('materials.manage');
    }

    public function unpublish(User $user): bool
    {
        return $user->hasPermission('materials.manage');
    }

    public function moveToTrash(User $user): bool
    {
        return $user->hasPermission('materials.manage');
    }

    public function restore(User $user): bool
    {
        return $user->hasPermission('materials.manage');
    }

    public function forceDelete(User $user): bool
    {
        return $user->hasPermission('materials.manage');
    }
}
