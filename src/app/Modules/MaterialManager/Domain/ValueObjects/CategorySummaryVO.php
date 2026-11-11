<?php

namespace App\Modules\MaterialManager\Domain\ValueObjects;

final readonly class CategorySummaryVO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $alias,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'alias' => $this->alias,
        ];
    }
}
