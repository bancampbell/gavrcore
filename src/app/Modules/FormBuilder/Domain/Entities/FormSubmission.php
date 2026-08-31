<?php

namespace App\Modules\FormBuilder\Domain\Entities;

class FormSubmission
{
    const STATUS_NEW = 'new';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';

    public function __construct(
        public readonly ?int $id,
        public int $formId,
        public ?int $userId,
        public array $data,
        public string $status,
        public ?array $meta,
        public ?\DateTimeImmutable $readAt,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public function markAsRead(): void
    {
        if ($this->readAt === null) {
            $this->readAt = new \DateTimeImmutable();
        }
    }

    public function isRead(): bool
    {
        return $this->readAt !== null;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'Новая',
            self::STATUS_IN_PROGRESS => 'В работе',
            self::STATUS_COMPLETED => 'Завершена',
            self::STATUS_REJECTED => 'Отклонена',
            default => $this->status,
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'blue',
            self::STATUS_IN_PROGRESS => 'yellow',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_REJECTED => 'red',
            default => 'gray',
        };
    }

    public function getSenderName(): string
    {
        $data = $this->data;
        $nameFields = ['name', 'Name', 'NAME', 'fullname', 'FullName', 'FULLNAME', 'fio', 'FIO', 'Fio'];
        foreach ($nameFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) return (string) $data[$field];
        }
        if (isset($data['email']) && !empty($data['email'])) return (string) $data['email'];
        if (isset($data['phone']) && !empty($data['phone'])) return (string) $data['phone'];
        return 'Аноним';
    }

    public function getSenderEmail(): ?string
    {
        $data = $this->data;
        $emailFields = ['email', 'Email', 'EMAIL', 'e-mail', 'mail'];
        foreach ($emailFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) return (string) $data[$field];
        }
        return null;
    }

    public function getSenderPhone(): ?string
    {
        $data = $this->data;
        $phoneFields = ['phone', 'Phone', 'PHONE', 'telephone', 'mobile', 'tel'];
        foreach ($phoneFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) return (string) $data[$field];
        }
        return null;
    }

    public function getDisplayName(): string
    {
        $name = $this->getSenderName();
        $email = $this->getSenderEmail();
        if ($email && $name !== $email) return $name . ' (' . $email . ')';
        return $name;
    }

    public function getContent(): string
    {
        $data = $this->data;
        $textFields = ['message', 'Message', 'MESSAGE', 'text', 'Text', 'TEXT', 'content', 'Content', 'CONTENT', 'comment', 'Comment', 'COMMENT'];
        foreach ($textFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) return (string) $data[$field];
        }
        $parts = [];
        if ($name = $this->getSenderName()) $parts[] = 'Имя: ' . $name;
        if ($email = $this->getSenderEmail()) $parts[] = 'Email: ' . $email;
        if ($phone = $this->getSenderPhone()) $parts[] = 'Телефон: ' . $phone;
        return implode("\n", $parts) ?: 'Нет текста';
    }

    public function getSubject(): string
    {
        $data = $this->data;
        $subjectFields = ['subject', 'Subject', 'SUBJECT', 'title', 'Title', 'TITLE', 'topic', 'Topic', 'TOPIC'];
        foreach ($subjectFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) return (string) $data[$field];
        }
        $firstLine = strtok($this->getContent(), "\n");
        if ($firstLine && strlen($firstLine) > 0) return $firstLine;
        return 'Заявка #' . $this->id;
    }
}