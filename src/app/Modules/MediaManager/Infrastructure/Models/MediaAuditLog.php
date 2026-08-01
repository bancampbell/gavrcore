<?php

namespace Modules\MediaManager\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaAuditLog extends Model
{
    protected $table = 'media_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'path',
        'old_path',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
