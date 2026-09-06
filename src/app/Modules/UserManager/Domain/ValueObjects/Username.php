<?php

namespace App\Modules\UserManager\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Username
{
    public function __construct(public string $value)
    {
        if (! preg_match('/^[a-z0-9_-]{2,50}$/', $value)) {
            throw new InvalidArgumentException(
                "Username may contain only lowercase latin letters, digits, dashes and underscores (2-50 chars): {$value}"
            );
        }
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
