<?php

namespace App\Modules\MenuManager\Domain\Events;
use App\Modules\MenuManager\Domain\ValueObjects\MenuItemId;
use App\Modules\MenuManager\Domain\ValueObjects\MenuStatus;

class MenuItemStatusChanged
{
    public function __construct(
        public readonly MenuItemId $id,
        public readonly MenuStatus $status,
    ) {}
}