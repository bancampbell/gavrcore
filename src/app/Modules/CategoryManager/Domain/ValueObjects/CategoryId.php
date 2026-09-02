<?php

namespace App\Modules\CategoryManager\Domain\ValueObjects;

class CategoryId
{
    public function __construct(
        public readonly int $value
    ) {
    }
}
