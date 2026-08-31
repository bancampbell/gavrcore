<?php

namespace App\Modules\MenuManager\Application\DTO;

class MenuTypeFiltersData
{
    public function __construct(public ?string $search, public ?bool $status) {}

    public static function fromArray(array $data): self
    {
        $status = null;
        if (isset($data['status'])) {
            $status = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        return new self(
            search: $data['search'] ?? null,
            status: $status,
        );
    }
}
