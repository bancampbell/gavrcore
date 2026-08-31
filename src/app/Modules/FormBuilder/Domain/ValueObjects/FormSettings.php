<?php

namespace App\Modules\FormBuilder\Domain\ValueObjects;

class FormSettings implements \JsonSerializable
{
    public function __construct(private array $data = []) {}
    public function toArray(): array { return $this->data; }
    public function get(string $key, mixed $default = null): mixed { return $this->data[$key] ?? $default; }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}