<?php

namespace App\Modules\CategoryManager\Domain\ValueObjects;

class CategoryDepth
{
    public function __construct(
        public readonly int $value
    ) {
    }
}
