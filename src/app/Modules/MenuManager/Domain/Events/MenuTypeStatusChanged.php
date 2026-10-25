<?php

namespace App\Modules\MenuManager\Domain\Events;

use App\Modules\MenuManager\Domain\ValueObjects\MenuStatus;
use App\Modules\MenuManager\Domain\ValueObjects\MenuTypeId;

class MenuTypeStatusChanged
{
    public function __construct(
        public readonly MenuTypeId $id,
        public readonly MenuStatus $status,
    ) {}
}
