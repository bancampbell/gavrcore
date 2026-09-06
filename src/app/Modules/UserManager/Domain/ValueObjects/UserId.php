<?php

namespace App\Modules\UserManager\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class UserId
{
    public function __construct(public int $value)
    {
        if ($this->value <= 0) {
            throw new InvalidArgumentException('User ID must be a positive integer.');
        }
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
