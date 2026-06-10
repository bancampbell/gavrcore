<?php

namespace App\DTO;

use Illuminate\Support\Str;

class UserData
{
    /**
     * @param array<int, int>|null $groups
     * @param array<int, int>|null $permissions
     */
    public function __construct(
        public readonly string $name,
        public readonly string $username,
        public readonly string $email,
        public readonly ?string $password,
        public readonly bool $blocked,
        public readonly bool $activated,
        public readonly ?array $groups,
        public readonly ?array $permissions,
        public readonly ?string $last_login_at,
        public readonly ?string $last_login_ip,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            username: $data['username'] ?? Str::slug($data['name']),
            email: $data['email'],
            password: $data['password'] ?? null,
            blocked: $data['blocked'] ?? false,
            activated: $data['activated'] ?? true,
            groups: $data['groups'] ?? [],
            permissions: $data['permissions'] ?? [],
            last_login_at: $data['last_login_at'] ?? null,
            last_login_ip: $data['last_login_ip'] ?? null,
        );
    }

    /**
     * @return array<string, string|bool|array<int>|null>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'blocked' => $this->blocked,
            'activated' => $this->activated,
            'last_login_at' => $this->last_login_at,
            'last_login_ip' => $this->last_login_ip,
        ], fn($value) => !is_null($value));
    }
}
