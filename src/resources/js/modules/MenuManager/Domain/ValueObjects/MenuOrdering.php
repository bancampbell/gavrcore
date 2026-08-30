<?php

namespace App\Modules\MenuManager\Domain\ValueObjects;

final class MenuOrdering
{
    public function __construct(private int $value) {}
    public function value(): int { return $this->value; }
}