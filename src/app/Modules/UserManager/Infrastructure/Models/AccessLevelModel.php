<?php

namespace App\Modules\UserManager\Infrastructure\Models;

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
 * @property-read Collection<int, GroupModel> $groups
 */
class AccessLevelModel extends Model
{
    protected $table = 'access_levels';

    protected $fillable = [
        'title', 'alias', 'description', 'ordering', 'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * @return BelongsToMany<GroupModel, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(GroupModel::class, 'access_level_group', 'access_level_id', 'group_id');
    }
}
