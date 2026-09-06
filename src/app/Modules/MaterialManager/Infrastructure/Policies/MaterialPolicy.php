<?php

namespace App\Modules\MaterialManager\Infrastructure\Policies;

use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use App\Modules\MaterialManager\Domain\Entities\Material;

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
        return $user->hasPermission('materials.manage') || $user->id === $material->userId;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('materials.manage');
    }

    public function update(User $user, Material $material): bool
    {
        return $user->hasPermission('materials.manage') || $user->id === $material->userId;
    }

    public function delete(User $user, Material $material): bool
    {
        return $user->hasPermission('materials.manage');
    }

    public function moveToTrash(User $user, ?Material $material = null): bool
    {
        return $user->hasPermission('materials.manage');
    }

    public function restore(User $user, ?Material $material = null): bool
    {
        return $user->hasPermission('materials.manage');
    }

    public function forceDelete(User $user, ?Material $material = null): bool
    {
        return $user->hasPermission('materials.manage');
    }

    public function publish(User $user, ?Material $material = null): bool
    {
        return $user->hasPermission('materials.manage');
    }

    public function unpublish(User $user, ?Material $material = null): bool
    {
        return $user->hasPermission('materials.manage');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('materials.manage');
    }
}
