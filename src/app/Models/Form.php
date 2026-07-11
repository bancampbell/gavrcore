<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $alias
 * @property string|null $description
 * @property array|null $fields
 * @property array|null $settings
 * @property bool $status
 * @property bool $is_dynamic
 * @property int $submissions_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FormSubmission> $submissions
 */
class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'alias',
        'description',
        'fields',
        'settings',
        'status',
        'is_dynamic',
        'submissions_count',
    ];

    protected $casts = [
        'fields' => 'array',
        'settings' => 'array',
        'status' => 'boolean',
        'is_dynamic' => 'boolean',
        'submissions_count' => 'integer',
    ];

    /**
     * @return HasMany<FormSubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }
}
