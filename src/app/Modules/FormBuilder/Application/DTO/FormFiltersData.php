<?php

namespace App\Modules\FormBuilder\Application\DTO;

class FormFiltersData
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?bool $status = null,
    ) {}
}