<?php

namespace App\DTO;

class PermissionData
{
    public function __construct(
        public readonly string $name,
        public readonly string $key,
        public readonly ?string $group,
        public readonly ?string $description,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            key: $data['key'],
            group: $data['group'] ?? null,
            description: $data['description'] ?? null,
        );
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'key' => $this->key,
            'group' => $this->group,
            'description' => $this->description,
        ], fn ($value) => ! is_null($value));
    }
}
