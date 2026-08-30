<?php

namespace App\Modules\MenuManager\Domain\ValueObjects;

final class MenuLanguage
{
    public function __construct(private string $value) {}
    public function value(): string { return $this->value; }
}