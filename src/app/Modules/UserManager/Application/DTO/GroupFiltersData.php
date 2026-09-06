<?php

namespace App\Modules\UserManager\Application\DTO;

final class GroupFiltersData
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?bool $status = null,
        public readonly int $perPage = 20,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            status: array_key_exists('status', $data) && $data['status'] !== null && $data['status'] !== ''
                ? filter_var($data['status'], FILTER_VALIDATE_BOOLEAN)
                : null,
            perPage: (int) ($data['per_page'] ?? config('user-manager.defaults.per_page', 20)),
        );
    }
}
