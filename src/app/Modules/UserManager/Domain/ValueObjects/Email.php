<?php

namespace App\Modules\UserManager\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Email
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = mb_strtolower(trim($value));

        if (! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: {$value}");
        }

        $this->value = $normalized;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
