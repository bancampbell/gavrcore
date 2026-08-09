<?php

namespace App\Modules\MaterialManager\Domain\Events;

use App\Modules\MaterialManager\Domain\Entities\Material;

abstract readonly class MaterialEvent
{
    public function __construct(
        public Material $material,
        public int $actorId,
    ) {}
}
