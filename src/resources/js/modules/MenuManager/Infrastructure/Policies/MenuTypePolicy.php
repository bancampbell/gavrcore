<?php

namespace App\Modules\MenuManager\Infrastructure\Policies;

use App\Models\User;

class MenuTypePolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user): bool { return true; }
    public function delete(User $user): bool { return true; }
}