<?php

namespace App\Modules\MenuManager\Domain\Entities;

use App\Modules\MenuManager\Domain\ValueObjects\MenuAlias;
use App\Modules\MenuManager\Domain\ValueObjects\MenuItemId;
use App\Modules\MenuManager\Domain\ValueObjects\MenuLanguage;
use App\Modules\MenuManager\Domain\ValueObjects\MenuLink;
use App\Modules\MenuManager\Domain\ValueObjects\MenuOrdering;
use App\Modules\MenuManager\Domain\ValueObjects\MenuStatus;
use App\Modules\MenuManager\Domain\ValueObjects\MenuTypeId;
use App\Modules\MenuManager\Domain\ValueObjects\MenuAccess;

class MenuItem
{
    public function __construct(
        private MenuItemId $id,
        private MenuTypeId $menuTypeId,
        private ?MenuItemId $parentId,
        private string $title,
        private MenuAlias $alias,
        private MenuLink $link,
        private string $target,
        private MenuOrdering $ordering,
        private MenuStatus $status,
        private MenuAccess $access,
        private MenuLanguage $language,
    ) {}

    public function id(): MenuItemId { return $this->id; }
    public function menuTypeId(): MenuTypeId { return $this->menuTypeId; }
    public function parentId(): ?MenuItemId { return $this->parentId; }
    public function title(): string { return $this->title; }
    public function alias(): MenuAlias { return $this->alias; }
    public function link(): MenuLink { return $this->link; }
    public function target(): string { return $this->target; }
    public function ordering(): MenuOrdering { return $this->ordering; }
    public function status(): MenuStatus { return $this->status; }
    public function access(): MenuAccess { return $this->access; }
    public function language(): MenuLanguage { return $this->language; }

    public function update(?int $parentId, string $title, ?string $alias, string $linkType, ?string $linkValue, string $target, ?bool $status, ?string $access, ?string $language): void
    {
        $this->parentId = $parentId !== null ? new MenuItemId($parentId) : null;
        $this->title = $title;
        if ($alias !== null) $this->alias = new MenuAlias($alias);
        $this->link = new MenuLink($linkType, $linkValue);
        $this->target = $target;
        if ($status !== null) $this->status = new MenuStatus($status);
        if ($access !== null) $this->access = new MenuAccess($access);
        if ($language !== null) $this->language = new MenuLanguage($language);
    }
}