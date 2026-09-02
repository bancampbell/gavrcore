<?php

namespace App\Modules\CategoryManager\Application\DTO;

class CategoryFiltersData
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?int $parent_id = null,
        public readonly ?bool $is_active = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            parent_id: isset($data['parent_id']) ? (int) $data['parent_id'] : null,
            is_active: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'search' => $this->search,
            'parent_id' => $this->parent_id,
            'is_active' => $this->is_active,
        ], fn ($v) => $v !== null);
    }
}
