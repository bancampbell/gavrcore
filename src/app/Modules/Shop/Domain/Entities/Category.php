<?php

namespace Modules\Shop\Domain\Entities;

class Category
{
    private string $id;
    private string $name;
    private string $slug;
    private ?string $parentId;

    public function __construct(
        string $id,
        string $name,
        string $slug,
        ?string $parentId = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->parentId = $parentId;
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getParentId(): ?string { return $this->parentId; }
}
