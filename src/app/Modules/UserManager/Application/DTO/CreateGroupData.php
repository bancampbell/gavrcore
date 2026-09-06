<?php

namespace App\Modules\UserManager\Application\DTO;

use Illuminate\Support\Str;

final class CreateGroupData
{
    /**
     * @param  array<int, int>  $permissionIds
     */
    public function __construct(
        public readonly string $name,
        public readonly string $alias,
        public readonly ?string $description,
        public readonly bool $status,
        public readonly int $ordering,
        public readonly array $permissionIds = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            alias: ! empty($data['alias']) ? $data['alias'] : Str::slug($data['name']),
            description: $data['description'] ?? null,
            status: (bool) ($data['status'] ?? true),
            ordering: (int) ($data['ordering'] ?? 0),
            permissionIds: array_map('intval', $data['permissions'] ?? []),
        );
    }
}
