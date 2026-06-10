<?php

namespace App\DTO;

class MenuTypeData
{
    public function __construct(
        public ?int $id,
        public string $title,
        public string $alias,
        public ?string $description,
        public int $ordering,
        public bool $status,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            title: $data['title'],
            alias: $data['alias'],
            description: $data['description'] ?? null,
            ordering: $data['ordering'] ?? 0,
            status: $data['status'] ?? true,
        );
    }

    /**
     * @return array<string, int|string|bool|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'alias' => $this->alias,
            'description' => $this->description,
            'ordering' => $this->ordering,
            'status' => $this->status,
        ];
    }
}
