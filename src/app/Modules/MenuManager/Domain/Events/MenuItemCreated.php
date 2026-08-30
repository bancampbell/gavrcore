<?php

namespace App\Modules\MenuManager\Domain\Events;
use App\Modules\MenuManager\Domain\Entities\MenuItem;

class MenuItemCreated
{
    public function __construct(public readonly MenuItem $menuitem) {}
}