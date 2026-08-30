<?php

namespace App\Modules\MenuManager\Domain\Entities;

use App\Modules\MenuManager\Domain\ValueObjects\MenuAlias;
use App\Modules\MenuManager\Domain\ValueObjects\MenuOrdering;
use App\Modules\MenuManager\Domain\ValueObjects\MenuStatus;
use App\Modules\MenuManager\Domain\ValueObjects\MenuTypeId;

class MenuType
{
    public function __construct(
        private MenuTypeId $id,
        private string $title,
        private MenuAlias $alias,
        private ?string $description,
        private MenuOrdering $ordering,
        private MenuStatus $status,
    ) {}

    public function id(): MenuTypeId { return $this->id; }
    public function title(): string { return $this->title; }
    public function alias(): MenuAlias { return $this->alias; }
    public function description(): ?string { return $this->description; }
    public function ordering(): MenuOrdering { return $this->ordering; }
    public function status(): MenuStatus { return $this->status; }

    public function update(string $title, ?string $alias, ?string $description, ?int $ordering, ?bool $status): void
    {
        $this->title = $title;
        if ($alias !== null) $this->alias = new MenuAlias($alias);
        if ($description !== null) $this->description = $description;
        if ($ordering !== null) $this->ordering = new MenuOrdering($ordering);
        if ($status !== null) $this->status = new MenuStatus($status);
    }
}