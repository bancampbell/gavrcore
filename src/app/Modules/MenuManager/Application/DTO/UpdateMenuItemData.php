<?php

namespace App\Modules\MenuManager\Application\DTO;

class UpdateMenuItemData
{
    public function __construct(
        public int|Optional|null $parentId = null,
        public ?string $title = null,
        public ?string $alias = null,
        public ?string $linkType = null,
        public ?string $linkValue = null,
        public ?string $target = null,
        public ?bool $status = null,
        public ?string $access = null,
        public ?string $language = null,
        public ?string $position = null,
        public ?int $afterId = null,
        public ?int $menuTypeId = null,
    ) {}
}
