<?php

namespace App\Modules\MaterialManager\Domain\ValueObjects;

final readonly class AuthorSummaryVO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
