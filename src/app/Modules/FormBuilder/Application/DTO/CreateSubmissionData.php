<?php

namespace App\Modules\FormBuilder\Application\DTO;

class CreateSubmissionData
{
    public function __construct(
        public readonly int $formId,
        public readonly ?int $userId,
        public readonly array $data,
        public readonly ?array $meta,
    ) {}
}