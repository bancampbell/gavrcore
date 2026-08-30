<?php

namespace App\Modules\MenuManager\Domain\ValueObjects;

final class MenuStatus
{
    public function __construct(private bool $value) {}
    public function value(): bool { return $this->value; }
    public function isActive(): bool { return $this->value; }
}