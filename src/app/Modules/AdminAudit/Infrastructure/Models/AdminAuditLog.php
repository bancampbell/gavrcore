<?php

namespace App\Modules\AdminAudit\Infrastructure\Models;

use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAuditLog extends Model
{
    protected $table = 'admin_audit_logs';

    protected $fillable = [
        'actor',
        'action',
        'subject_type',
        'subject_id',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'actor');
    }
}
