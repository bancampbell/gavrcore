<?php

namespace App\Modules\FormBuilder\Application\DTO;

class CreateFormData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $alias,
        public readonly ?string $description,
        public readonly array $fields,
        public readonly array $settings,
        public readonly array $notificationEmails,
        public readonly bool $status,
        public readonly bool $isDynamic,
    ) {}
}