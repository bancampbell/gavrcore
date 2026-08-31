<?php

namespace App\Modules\FormBuilder\Domain\Entities;

use App\Modules\FormBuilder\Domain\ValueObjects\FormFieldCollection;
use App\Modules\FormBuilder\Domain\ValueObjects\FormSettings;
use App\Modules\FormBuilder\Domain\ValueObjects\FormStatus;

class Form
{
    public function __construct(
        public readonly ?int $id,
        public string $title,
        public string $alias,
        public ?string $description,
        public FormFieldCollection $fields,
        public FormSettings $settings,
        public array $notificationEmails,
        public FormStatus $status,
        public bool $isDynamic,
        public int $submissionsCount,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public function update(array $data): void
    {
        if (isset($data['title'])) $this->title = $data['title'];
        if (isset($data['alias'])) $this->alias = $data['alias'];
        if (array_key_exists('description', $data)) $this->description = $data['description'];
        if (isset($data['fields'])) $this->fields = $data['fields'] instanceof FormFieldCollection ? $data['fields'] : new FormFieldCollection($data['fields']);
        if (isset($data['settings'])) $this->settings = $data['settings'] instanceof FormSettings ? $data['settings'] : new FormSettings($data['settings']);
        if (array_key_exists('notification_emails', $data)) $this->notificationEmails = $data['notification_emails'];
        if (isset($data['status'])) $this->status = $data['status'] instanceof FormStatus ? $data['status'] : new FormStatus($data['status']);
        if (isset($data['is_dynamic'])) $this->isDynamic = $data['is_dynamic'];
    }

    public function incrementSubmissions(): void
    {
        $this->submissionsCount++;
    }
}