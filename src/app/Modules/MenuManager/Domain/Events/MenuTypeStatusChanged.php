<?php

namespace App\Modules\MenuManager\Domain\Events;
use App\Modules\MenuManager\Domain\ValueObjects\MenuTypeId;
use App\Modules\MenuManager\Domain\ValueObjects\MenuStatus;

class MenuTypeStatusChanged
{
    public function __construct(
        public readonly MenuTypeId $id,
        public readonly MenuStatus $status,
    ) {}
}