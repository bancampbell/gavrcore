<?php

namespace App\Modules\UserManager\Application\DTO;

use Illuminate\Support\Str;

final class CreateUserData
{
    /**
     * @param  array<int, int>  $groupIds
     */
    public function __construct(
        public readonly string $name,
        public readonly string $username,
        public readonly string $email,
        public readonly string $password,
        public readonly bool $blocked,
        public readonly bool $activated,
        public readonly array $groupIds = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            username: $data['username'] ?? Str::slug($data['name']),
            email: $data['email'],
            password: $data['password'],
            blocked: (bool) ($data['blocked'] ?? false),
            activated: (bool) ($data['activated'] ?? true),
            groupIds: array_map('intval', $data['groups'] ?? []),
        );
    }
}
