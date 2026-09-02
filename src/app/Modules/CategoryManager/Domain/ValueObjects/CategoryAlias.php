<?php

namespace App\Modules\CategoryManager\Domain\ValueObjects;

class CategoryAlias
{
    public function __construct(
        public readonly string $value
    ) {
    }
}
