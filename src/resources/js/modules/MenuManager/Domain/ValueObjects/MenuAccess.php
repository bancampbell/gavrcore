<?php

namespace App\Modules\MenuManager\Domain\ValueObjects;

final class MenuAccess
{
    public function __construct(private string $value) {}
    public function value(): string { return $this->value; }
}