<?php

namespace App\Modules\CategoryManager\Application\DTO;

class CategoryData
{
    /**
     * @param  array<string>  $fields
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $alias = null,
        public readonly ?string $description = null,
        public readonly ?int $parent_id = null,
        public readonly ?bool $is_active = null,
        private readonly array $fields = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            alias: $data['alias'] ?? null,
            description: $data['description'] ?? null,
            parent_id: $data['parent_id'] ?? null,
            is_active: $data['is_active'] ?? null,
            fields: array_keys($data),
        );
    }

    public function hasField(string $field): bool
    {
        return in_array($field, $this->fields, true);
    }

    /**
     * @return array<string, string|int|bool|null>
     */
    public function toArray(): array
    {
        $all = [
            'name' => $this->name,
            'alias' => $this->alias,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'is_active' => $this->is_active,
        ];

        return array_intersect_key($all, array_flip($this->fields));
    }
}
