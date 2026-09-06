<?php

namespace App\Modules\UserManager\Application\DTO;

final class UserFiltersData
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?bool $blocked = null,
        public readonly ?bool $activated = null,
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
            blocked: array_key_exists('blocked', $data) && $data['blocked'] !== null && $data['blocked'] !== ''
                ? filter_var($data['blocked'], FILTER_VALIDATE_BOOLEAN)
                : null,
            activated: array_key_exists('activated', $data) && $data['activated'] !== null && $data['activated'] !== ''
                ? filter_var($data['activated'], FILTER_VALIDATE_BOOLEAN)
                : null,
            perPage: (int) ($data['per_page'] ?? config('user-manager.defaults.per_page', 20)),
        );
    }
}
