<?php

namespace App\Modules\CategoryManager\Infrastructure\Policies;

use App\Models\User;
use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;

class CategoryPolicy
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
        return $user->hasPermission('categories.manage');
    }

    public function view(User $user, CategoryModel $category): bool
    {
        return $user->hasPermission('categories.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('categories.manage');
    }

    public function update(User $user, CategoryModel $category): bool
    {
        return $user->hasPermission('categories.manage');
    }

    public function delete(User $user, CategoryModel $category): bool
    {
        return $user->hasPermission('categories.manage');
    }
}
