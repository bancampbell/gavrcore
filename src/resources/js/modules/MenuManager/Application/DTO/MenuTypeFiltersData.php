<?php

namespace App\Modules\MenuManager\Application\DTO;

class MenuTypeFiltersData
{
    public function __construct(public ?string $search, public ?bool $status) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            status: isset($data['status']) ? (bool) $data['status'] : null,
        );
    }
}