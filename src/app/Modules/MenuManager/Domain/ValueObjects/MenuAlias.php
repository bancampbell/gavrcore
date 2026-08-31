<?php

namespace App\Modules\MenuManager\Domain\ValueObjects;

final class MenuAlias
{
    public function __construct(private string $value) {}
    public function value(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}
