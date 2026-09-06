<?php

namespace App\Modules\UserManager\Domain\Events;

use App\Modules\UserManager\Domain\Entities\Permission;

final class PermissionCreated
{
    public function __construct(
        public readonly Permission $permission,
    ) {
    }
}
