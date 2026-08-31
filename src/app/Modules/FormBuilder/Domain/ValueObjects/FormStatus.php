<?php

namespace App\Modules\FormBuilder\Domain\ValueObjects;

class FormStatus implements \JsonSerializable
{
    public function __construct(private bool $value) {}

    public function value(): bool { return $this->value; }
    public function isPublished(): bool { return $this->value === true; }
    public function isDraft(): bool { return $this->value === false; }
    public function label(): string { return $this->value ? 'Опубликовано' : 'Черновик'; }

    public function jsonSerialize(): mixed
    {
        return $this->value;
    }
}