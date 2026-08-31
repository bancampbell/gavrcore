<?php

namespace App\Modules\FormBuilder\Application\DTO;

class SubmissionFiltersData
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $status = null,
        public readonly ?int $formId = null,
    ) {}
}