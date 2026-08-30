<?php

namespace App\Modules\MenuManager\Domain\Events;
use App\Modules\MenuManager\Domain\Entities\MenuType;

class MenuTypeCreated
{
    public function __construct(public readonly MenuType $menutype) {}
}