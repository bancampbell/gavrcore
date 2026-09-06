<?php

namespace App\Modules\UserManager\Domain\Events;

use App\Modules\UserManager\Domain\Entities\Group;

final class GroupStatusChanged
{
    public function __construct(
        public readonly Group $group,
        public readonly bool $status,
    ) {
    }
}
