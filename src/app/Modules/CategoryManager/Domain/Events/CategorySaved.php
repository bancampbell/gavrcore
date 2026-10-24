<?php

namespace App\Modules\CategoryManager\Domain\Events;

final readonly class CategorySaved
{
    public function __construct(
        public int $categoryId,
    ) {}
}
