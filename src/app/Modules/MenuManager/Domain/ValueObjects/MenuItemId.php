<?php

namespace App\Modules\MenuManager\Domain\ValueObjects;

final class MenuItemId
{
    public function __construct(private int $value) {}
    public function value(): int { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}