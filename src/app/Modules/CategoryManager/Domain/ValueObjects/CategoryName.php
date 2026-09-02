<?php

namespace App\Modules\CategoryManager\Domain\ValueObjects;

class CategoryName
{
    public function __construct(
        public readonly string $value
    ) {
    }
}
