<?php

namespace App\DTO;

use Illuminate\Support\Str;

class CategoryData
{
    public function __construct(
        public readonly string $name,
        public readonly string $alias,
        public readonly ?string $description,
        public readonly ?int $parent_id,
        public readonly int $lft,
        public readonly int $rgt,
        public readonly int $depth,
        public readonly bool $is_active,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            alias: $data['alias'] ?? Str::slug($data['name']),
            description: $data['description'] ?? null,
            parent_id: $data['parent_id'] ?? null,
            lft: $data['lft'] ?? 0,
            rgt: $data['rgt'] ?? 0,
            depth: $data['depth'] ?? 0,
            is_active: $data['is_active'] ?? true,
        );
    }

    /**
     * @return array<string, string|int|bool|null>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'alias' => $this->alias,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'lft' => $this->lft,
            'rgt' => $this->rgt,
            'depth' => $this->depth,
            'is_active' => $this->is_active,
        ];
    }
}
