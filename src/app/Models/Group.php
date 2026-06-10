<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\AccessLevel;

/**
 * @property int $id
 * @property string $name
 * @property string $alias
 * @property string|null $description
 * @property bool $status
 * @property int $ordering
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Permission> $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AccessLevel> $accessLevels
 */
class Group extends Model
{
    protected $fillable = [
        'name', 'alias', 'description', 'status', 'ordering'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'group_permissions');
    }

    /**
     * @return BelongsToMany<AccessLevel, $this>
     */
    public function accessLevels(): BelongsToMany
    {
        return $this->belongsToMany(AccessLevel::class, 'access_level_group');
    }
}
