<?php

namespace App\Modules\CategoryManager\Domain\Events;

use App\Modules\CategoryManager\Domain\Entities\Category;

class CategoryCreated
{
    public function __construct(
        public readonly Category $category
    ) {
    }
}
