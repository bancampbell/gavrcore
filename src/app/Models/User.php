<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string|null $password
 * @property bool $blocked
 * @property bool $activated
 * @property string|null $last_login_at
 * @property string|null $last_login_ip
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection<int, Group> $groups
 * @property-read Collection<int, Permission> $permissions
 * @property-read array<int, string> $all_permissions
 *
 * @method static \Illuminate\Database\Eloquent\Factories\Factory<static> factory()
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'blocked',
        'activated',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'blocked' => 'boolean',
        'activated' => 'boolean',
    ];

    /**
     * @return BelongsToMany<Group, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    /**
     * @return array<int, string>
     */
    public function getAllPermissionsAttribute(): array
    {
        $permissions = $this->permissions->pluck('key')->toArray();

        foreach ($this->groups as $group) {
            $permissions = array_merge($permissions, $group->permissions->pluck('key')->toArray());
        }

        return array_unique($permissions);
    }

    public function hasPermission(string $key): bool
    {
        return in_array($key, $this->all_permissions);
    }
}
