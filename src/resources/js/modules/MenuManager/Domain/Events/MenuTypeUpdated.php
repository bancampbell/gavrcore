<?php

namespace App\Modules\MenuManager\Domain\Events;
use App\Modules\MenuManager\Domain\Entities\MenuType;

class MenuTypeUpdated
{
    public function __construct(public readonly MenuType $menutype) {}
}