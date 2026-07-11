<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $form_id
 * @property array $data
 * @property array|null $meta
 * @property Carbon|null $read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Form $form
 */
class FormSubmission extends Model
{
    protected $fillable = [
        'form_id',
        'data',
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
     * Получить имя отправителя из данных формы
     */
    public function getSenderNameAttribute(): string
    {
        $data = $this->data;

        // Ищем поле name, имя, fullname, fio
        $nameFields = ['name', 'Name', 'NAME', 'fullname', 'FullName', 'FULLNAME', 'fio', 'FIO', 'Fio'];
        foreach ($nameFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                return (string) $data[$field];
            }
        }

        // Если ничего не нашли - берем email или phone
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

        // Если имя = email или телефон - показываем без изменений
        $email = $this->sender_email;
        $phone = $this->sender_phone;

        if ($name === $email) {
            return $name;
        }

        if ($name === $phone) {
            return $name;
        }

        // Если есть имя + email - показываем имя (email)
        if ($email && $name !== $email) {
            return $name . ' (' . $email . ')';
        }

        return $name;
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
}
