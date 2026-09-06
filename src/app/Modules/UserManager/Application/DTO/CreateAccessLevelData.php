<?php

namespace App\Modules\UserManager\Application\DTO;

use Illuminate\Support\Str;

final class CreateAccessLevelData
{
    /**
     * @param  array<int, int>  $groupIds
     */
    public function __construct(
        public readonly string $title,
        public readonly string $alias,
        public readonly ?string $description,
        public readonly bool $status,
        public readonly array $groupIds = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            alias: ! empty($data['alias']) ? $data['alias'] : Str::slug($data['title']),
            description: $data['description'] ?? null,
            status: (bool) ($data['status'] ?? true),
            groupIds: array_map('intval', $data['groups'] ?? []),
        );
    }
}
