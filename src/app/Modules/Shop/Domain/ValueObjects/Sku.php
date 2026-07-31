<?php

namespace Modules\Shop\Domain\ValueObjects;

class Sku
{
    private string $value;

    public function __construct(string $value)
    {
        if (!preg_match('/^[A-Z0-9\-_]{3,50}$/', $value)) {
            throw new \InvalidArgumentException('SKU must be 3-50 chars, uppercase letters, digits, dash, underscore');
        }
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(Sku $other): bool
    {
        return $this->value === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
