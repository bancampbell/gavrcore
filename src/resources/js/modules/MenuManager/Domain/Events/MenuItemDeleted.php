<?php

namespace App\Modules\MenuManager\Domain\Events;
use App\Modules\MenuManager\Domain\ValueObjects\MenuItemId;

class MenuItemDeleted
{
    public function __construct(public readonly MenuItemId $id) {}
}