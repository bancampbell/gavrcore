<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Material extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'category_id',
        'user_id',
        'state',
        'access',
        'views',
        'published_at',
        'featured',
        'show_on_homepage',
        'show_date',
        'show_author',
        'show_category',
        'show_views',
        'use_global_settings',
        'template', // ← ДОБАВЛЕНО
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'views' => 'integer',
        'featured' => 'boolean',
        'show_on_homepage' => 'boolean',
        'show_date' => 'boolean',
        'show_author' => 'boolean',
        'show_category' => 'boolean',
        'show_views' => 'boolean',
        'use_global_settings' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'slug',
                'state',
                'category_id',
                'published_at',
                'featured',
                'show_on_homepage',
                'template', // ← ДОБАВИЛ В ЛОГИ
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(function (string $eventName) {
                $eventMap = [
                    'created' => 'Создан материал',
                    'updated' => 'Обновлен материал',
                    'deleted' => 'Удален материал',
                    'restored' => 'Восстановлен материал из корзины',
                ];
                return $eventMap[$eventName] ?? 'Изменен материал';
            });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accessLevel(): BelongsTo
    {
        return $this->belongsTo(AccessLevel::class, 'access_level_id');
    }
}
