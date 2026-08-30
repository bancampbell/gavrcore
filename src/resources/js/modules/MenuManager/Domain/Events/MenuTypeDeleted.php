<?php

namespace App\Modules\MenuManager\Domain\Events;
use App\Modules\MenuManager\Domain\ValueObjects\MenuTypeId;

class MenuTypeDeleted
{
    public function __construct(public readonly MenuTypeId $id) {}
}