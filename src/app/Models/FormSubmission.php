<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $form_id
 * @property int|null $user_id
 * @property array $data
 * @property string $status
 * @property array|null $meta
 * @property Carbon|null $read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Form $form
 * @property-read User|null $user
 */
class FormSubmission extends Model
{
    const STATUS_NEW = 'new';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'form_id',
        'user_id',
        'data',
        'status',
        'meta',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'meta' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Связь с формой
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Проверить, прочитано ли сообщение
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Отметить как прочитанное
     */
    public function markAsRead(): self
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }

        return $this;
    }

    /**
     * Получить статус на русском
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'Новая',
            self::STATUS_IN_PROGRESS => 'В работе',
            self::STATUS_COMPLETED => 'Завершена',
            self::STATUS_REJECTED => 'Отклонена',
            default => $this->status,
        };
    }

    /**
     * Получить цвет статуса для отображения
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'blue',
            self::STATUS_IN_PROGRESS => 'yellow',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_REJECTED => 'red',
            default => 'gray',
        };
    }

    /**
     * Получить имя отправителя из данных формы
     */
    public function getSenderNameAttribute(): string
    {
        $data = $this->data;

        $nameFields = ['name', 'Name', 'NAME', 'fullname', 'FullName', 'FULLNAME', 'fio', 'FIO', 'Fio'];
        foreach ($nameFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                return (string) $data[$field];
            }
        }

        if (isset($data['email']) && !empty($data['email'])) {
            return (string) $data['email'];
        }

        if (isset($data['phone']) && !empty($data['phone'])) {
            return (string) $data['phone'];
        }

        return 'Аноним';
    }

    /**
     * Получить email отправителя из данных формы
     */
    public function getSenderEmailAttribute(): ?string
    {
        $data = $this->data;

        $emailFields = ['email', 'Email', 'EMAIL', 'e-mail', 'mail'];
        foreach ($emailFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                return (string) $data[$field];
            }
        }

        return null;
    }

    /**
     * Получить телефон отправителя из данных формы
     */
    public function getSenderPhoneAttribute(): ?string
    {
        $data = $this->data;

        $phoneFields = ['phone', 'Phone', 'PHONE', 'telephone', 'mobile', 'tel'];
        foreach ($phoneFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                return (string) $data[$field];
            }
        }

        return null;
    }

    /**
     * Получить отображаемое имя для списка
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->sender_name;
        $email = $this->sender_email;
        $phone = $this->sender_phone;

        if ($name === $email) {
            return $name;
        }

        if ($name === $phone) {
            return $name;
        }

        if ($email && $name !== $email) {
            return $name . ' (' . $email . ')';
        }

        return $name;
    }

    /**
     * Получить текстовое содержимое заявки
     */
    public function getContentAttribute(): string
    {
        $data = $this->data;

        // Пытаемся найти текст
        $textFields = ['message', 'Message', 'MESSAGE', 'text', 'Text', 'TEXT', 'content', 'Content', 'CONTENT', 'comment', 'Comment', 'COMMENT'];
        foreach ($textFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                return (string) $data[$field];
            }
        }

        // Если есть только имя и телефон — формируем из них
        $parts = [];
        if ($this->sender_name) $parts[] = 'Имя: ' . $this->sender_name;
        if ($this->sender_email) $parts[] = 'Email: ' . $this->sender_email;
        if ($this->sender_phone) $parts[] = 'Телефон: ' . $this->sender_phone;

        return implode("\n", $parts) ?: 'Нет текста';
    }

    /**
     * Получить тему заявки (первая строка или название поля)
     */
    public function getSubjectAttribute(): string
    {
        $data = $this->data;

        $subjectFields = ['subject', 'Subject', 'SUBJECT', 'title', 'Title', 'TITLE', 'topic', 'Topic', 'TOPIC'];
        foreach ($subjectFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                return (string) $data[$field];
            }
        }

        $firstLine = strtok($this->content, "\n");
        if ($firstLine && strlen($firstLine) > 0) {
            return $firstLine;
        }

        return 'Заявка #' . $this->id;
    }

    /**
     * Scope: только непрочитанные
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: только прочитанные
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope: по статусу
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: по пользователю
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
