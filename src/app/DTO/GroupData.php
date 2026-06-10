<?php

namespace App\DTO;

use Illuminate\Support\Str;

class GroupData
{
    public function __construct(
        public readonly string $name,
        public readonly string $alias,
        public readonly ?string $description,
        public readonly bool $status,
        public readonly int $ordering,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            alias: $data['alias'] ?? Str::slug($data['name']),
            description: $data['description'] ?? null,
            status: $data['status'] ?? true,
            ordering: $data['ordering'] ?? 0,
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
            'status' => $this->status,
            'ordering' => $this->ordering,
        ];
    }
}
