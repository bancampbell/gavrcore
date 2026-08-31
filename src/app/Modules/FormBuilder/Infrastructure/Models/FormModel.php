<?php

namespace App\Modules\FormBuilder\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormModel extends Model
{
    use HasFactory;

    protected $table = 'forms';

    protected $fillable = [
        'title', 'alias', 'description', 'fields', 'settings',
        'notification_emails', 'status', 'is_dynamic', 'submissions_count',
    ];

    protected $casts = [
        'fields' => 'array',
        'settings' => 'array',
        'notification_emails' => 'array',
        'status' => 'boolean',
        'is_dynamic' => 'boolean',
        'submissions_count' => 'integer',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmissionModel::class, 'form_id');
    }
}