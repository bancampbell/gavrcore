<?php

namespace App\Modules\FormBuilder\Application\DTO;

class UpdateFormFieldsData
{
    public function __construct(
        public readonly int $formId,
        public readonly array $fields,
    ) {}
}