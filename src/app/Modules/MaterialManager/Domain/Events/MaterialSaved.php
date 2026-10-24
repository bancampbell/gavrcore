<?php

namespace App\Modules\MaterialManager\Domain\Events;

final readonly class MaterialSaved
{
    public function __construct(
        public int $materialId,
    ) {}
}
