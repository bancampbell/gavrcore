<?php

namespace App\Modules\UserManager\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Alias
{
    public function __construct(public string $value)
    {
        if (! preg_match('/^[a-z0-9-]+$/', $value)) {
            throw new InvalidArgumentException(
                "Alias may contain only lowercase latin letters, digits and dashes: {$value}"
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
