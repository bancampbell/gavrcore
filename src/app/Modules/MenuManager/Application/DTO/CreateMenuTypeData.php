<?php

namespace App\Modules\MenuManager\Application\DTO;

class CreateMenuTypeData
{
    public function __construct(
        public string $title,
        public ?string $alias,
        public ?string $description,
        public int $ordering,
        public bool $status,
    ) {}
}