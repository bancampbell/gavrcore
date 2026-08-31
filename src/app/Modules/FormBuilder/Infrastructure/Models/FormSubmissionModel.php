<?php

namespace App\Modules\FormBuilder\Infrastructure\Models;

use App\Modules\FormBuilder\Domain\Entities\FormSubmission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmissionModel extends Model
{
    protected $table = 'form_submissions';

    const STATUS_NEW = 'new';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'form_id', 'user_id', 'data', 'status', 'meta', 'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'meta' => 'array',
        'read_at' => 'datetime',
    ];

    protected $appends = ['display_name'];

    public function getDisplayNameAttribute(): string
    {
        return (new FormSubmission(
            id: $this->id,
            formId: $this->form_id,
            userId: $this->user_id,
            data: $this->data ?? [],
            status: $this->status ?? FormSubmission::STATUS_NEW,
            meta: $this->meta,
            readAt: $this->read_at ? new \DateTimeImmutable($this->read_at) : null,
        ))->getDisplayName();
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormModel::class, 'form_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}