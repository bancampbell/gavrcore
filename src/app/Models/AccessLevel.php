<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $alias
 * @property string|null $description
 * @property int $ordering
 * @property bool $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection<int, Group> $groups
 */
class AccessLevel extends Model
{
    protected $table = 'access_levels';

    protected $fillable = [
        'title', 'alias', 'description', 'ordering', 'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * @return BelongsToMany<Group, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'access_level_group');
    }
}
