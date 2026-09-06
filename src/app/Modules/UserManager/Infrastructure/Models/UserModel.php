<?php

namespace App\Modules\UserManager\Infrastructure\Models;

use App\Modules\UserManager\Database\Factories\UserFactory;
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
 * @property string $password
 * @property bool $blocked
 * @property bool $activated
 * @property Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection<int, GroupModel> $groups
 * @property-read Collection<int, PermissionModel> $permissions
 */
class UserModel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

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
        'last_login_at' => 'datetime',
    ];

    /**
     * @return BelongsToMany<GroupModel, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(GroupModel::class, 'group_user', 'user_id', 'group_id');
    }

    /**
     * @return BelongsToMany<PermissionModel, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(PermissionModel::class, 'user_permissions', 'user_id', 'permission_id');
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

    /**
     * Проверяет, является ли пользователь администратором
     */
    public function isAdmin(): bool
    {
        return $this->hasPermission('admin.access');
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
