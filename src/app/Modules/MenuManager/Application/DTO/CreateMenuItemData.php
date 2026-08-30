<?php

namespace App\Modules\MenuManager\Application\DTO;

class CreateMenuItemData
{
    public function __construct(
        public int $menuTypeId,
        public ?int $parentId,
        public string $title,
        public ?string $alias,
        public string $linkType,
        public ?string $linkValue,
        public string $target,
        public bool $status,
        public string $access,
        public string $language,
        public ?string $position,
        public ?int $afterId,
    ) {}
}