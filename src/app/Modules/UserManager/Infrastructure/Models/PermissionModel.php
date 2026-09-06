<?php

namespace App\Modules\UserManager\Infrastructure\Models;

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
 * @property-read Collection<int, UserModel> $users
 * @property-read Collection<int, GroupModel> $groups
 */
class PermissionModel extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'name', 'key', 'group', 'description',
    ];

    /**
     * @return BelongsToMany<UserModel, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(UserModel::class, 'user_permissions', 'permission_id', 'user_id');
    }

    /**
     * @return BelongsToMany<GroupModel, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(GroupModel::class, 'group_permissions', 'permission_id', 'group_id');
    }
}
