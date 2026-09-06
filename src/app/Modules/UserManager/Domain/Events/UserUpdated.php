<?php

namespace App\Modules\UserManager\Domain\Events;

use App\Modules\UserManager\Domain\Entities\User;

final class UserUpdated
{
    public function __construct(
        public readonly User $user,
    ) {
    }
}
