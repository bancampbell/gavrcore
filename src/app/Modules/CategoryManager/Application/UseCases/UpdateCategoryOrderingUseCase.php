<?php

namespace App\Modules\CategoryManager\Application\UseCases;

use App\Modules\CategoryManager\Infrastructure\Services\CategoryTreeBuilder;

class UpdateCategoryOrderingUseCase
{
    public function __construct(
        protected CategoryTreeBuilder $treeBuilder
    ) {
    }

    public function execute(): void
    {
        $this->treeBuilder->rebuildNestedSet();
    }
}
