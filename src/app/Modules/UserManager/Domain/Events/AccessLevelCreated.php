<?php

namespace App\Modules\UserManager\Domain\Events;

use App\Modules\UserManager\Domain\Entities\AccessLevel;

final class AccessLevelCreated
{
    public function __construct(
        public readonly AccessLevel $accessLevel,
    ) {
    }
}
