<?php

namespace App\Modules\CategoryManager\Domain\Entities;

use App\Modules\CategoryManager\Domain\ValueObjects\CategoryAlias;
use App\Modules\CategoryManager\Domain\ValueObjects\CategoryDepth;
use App\Modules\CategoryManager\Domain\ValueObjects\CategoryId;
use App\Modules\CategoryManager\Domain\ValueObjects\CategoryName;
use App\Modules\CategoryManager\Domain\ValueObjects\CategoryStatus;

class Category
{
    public function __construct(
        public readonly CategoryId $id,
        public readonly CategoryName $name,
        public readonly CategoryAlias $alias,
        public readonly ?string $description,
        public readonly ?CategoryId $parentId,
        public readonly int $lft,
        public readonly int $rgt,
        public readonly CategoryDepth $depth,
        public readonly CategoryStatus $status,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: new CategoryId($data['id'] ?? 0),
            name: new CategoryName($data['name']),
            alias: new CategoryAlias($data['alias']),
            description: $data['description'] ?? null,
            parentId: isset($data['parent_id']) ? new CategoryId($data['parent_id']) : null,
            lft: $data['lft'] ?? 0,
            rgt: $data['rgt'] ?? 0,
            depth: new CategoryDepth($data['depth'] ?? 0),
            status: new CategoryStatus($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'name' => $this->name->value,
            'alias' => $this->alias->value,
            'description' => $this->description,
            'parent_id' => $this->parentId?->value,
            'lft' => $this->lft,
            'rgt' => $this->rgt,
            'depth' => $this->depth->value,
            'is_active' => $this->status->value,
        ];
    }
}
