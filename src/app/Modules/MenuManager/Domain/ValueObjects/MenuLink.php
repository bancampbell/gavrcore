<?php

namespace App\Modules\MenuManager\Domain\ValueObjects;

final class MenuLink
{
    public function __construct(private string $type, private ?string $value) {}
    public function type(): string { return $this->type; }
    public function value(): ?string { return $this->value; }
}