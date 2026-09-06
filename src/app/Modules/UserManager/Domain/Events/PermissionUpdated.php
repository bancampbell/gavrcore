<?php

namespace App\Modules\UserManager\Domain\Events;

use App\Modules\UserManager\Domain\Entities\Permission;

final class PermissionUpdated
{
    public function __construct(
        public readonly Permission $permission,
    ) {
    }
}
