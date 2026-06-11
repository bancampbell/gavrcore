<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $key
 * @property string|null $group
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection<int, User> $users
 * @property-read Collection<int, Group> $groups
 */
class Permission extends Model
{
    protected $fillable = [
        'name', 'key', 'group', 'description',
    ];

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions');
    }

    /**
     * @return BelongsToMany<Group, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_permissions');
    }
}
