<?php

namespace App\Modules\UserManager\Domain\Events;

use App\Modules\UserManager\Domain\Entities\Group;

final class GroupCreated
{
    public function __construct(
        public readonly Group $group,
    ) {
    }
}
