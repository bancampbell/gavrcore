<?php

namespace App\Modules\CategoryManager\Domain\Events;

class CategoryDeleted
{
    public function __construct(
        public readonly int $categoryId
    ) {
    }
}
