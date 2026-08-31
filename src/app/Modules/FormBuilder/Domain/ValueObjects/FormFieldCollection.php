<?php

namespace App\Modules\FormBuilder\Domain\ValueObjects;

class FormFieldCollection implements \JsonSerializable
{
    private array $fields;

    public function __construct(array $fields = []) { $this->fields = $fields; }
    public function toArray(): array { return $this->fields; }
    public function count(): int { return count($this->fields); }

    public function jsonSerialize(): mixed
    {
        return $this->fields;
    }
}