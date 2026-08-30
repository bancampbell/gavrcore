<?php

namespace App\Modules\MenuManager\Domain\Services;

interface MenuTreeBuilderInterface
{
    public function buildTree(array $items, ?int $parentId = null): array;
}