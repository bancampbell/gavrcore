<?php

namespace App\Modules\UserManager\Application\DTO;

final class LoginData
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
        );
    }
}
