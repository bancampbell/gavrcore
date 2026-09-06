<?php

namespace App\Modules\UserManager\Domain\Events;

use App\Modules\UserManager\Domain\Entities\AccessLevel;

final class AccessLevelStatusChanged
{
    public function __construct(
        public readonly AccessLevel $accessLevel,
        public readonly bool $status,
    ) {
    }
}
