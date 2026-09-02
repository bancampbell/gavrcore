<?php

namespace App\Modules\CategoryManager\Domain\ValueObjects;

class CategoryStatus
{
    public function __construct(
        public readonly bool $value
    ) {
    }
}
