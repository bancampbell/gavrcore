<?php

namespace App\Modules\UserManager\Application\DTO;

use Illuminate\Support\Str;

final class RegisterData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $username,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            username: $data['username'] ?? Str::slug(strstr($data['email'], '@', true)),
        );
    }
}
