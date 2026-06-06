<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    public function getAllPermissionsAttribute()
    {
        // Получаем права пользователя + права из групп
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
