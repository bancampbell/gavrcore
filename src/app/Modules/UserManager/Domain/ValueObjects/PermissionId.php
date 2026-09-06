<?php

namespace App\Modules\UserManager\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class PermissionId
{
    public function __construct(public int $value)
    {
        if ($this->value <= 0) {
            throw new InvalidArgumentException('Permission ID must be a positive integer.');
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
