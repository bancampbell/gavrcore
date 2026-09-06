<?php

namespace App\Modules\UserManager\Application\DTO;

final class UpdateUserData
{
    /**
     * @param  Optional<string>  $password
     * @param  Optional<array<int, int>>  $groupIds
     */
    public function __construct(
        public readonly string $name,
        public readonly string $username,
        public readonly string $email,
        public readonly Optional $password,
        public readonly bool $blocked,
        public readonly bool $activated,
        public readonly Optional $groupIds,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            username: $data['username'],
            email: $data['email'],
            password: ! empty($data['password'])
                ? Optional::of($data['password'])
                : Optional::empty(),
            blocked: (bool) ($data['blocked'] ?? false),
            activated: (bool) ($data['activated'] ?? true),
            groupIds: array_key_exists('groups', $data) && is_array($data['groups'])
                ? Optional::of(array_map('intval', $data['groups']))
                : Optional::empty(),
        );
    }
}
