<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;

class CategoryPolicy
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
        return $user->hasPermission('categories.manage');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('categories.manage');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.manage');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermission('categories.manage');
    }
}
