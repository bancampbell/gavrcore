<?php

namespace App\Modules\UserManager\Infrastructure\Models;

use App\Modules\UserManager\Database\Factories\GroupFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $alias
 * @property string|null $description
 * @property bool $status
 * @property int $ordering
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection<int, UserModel> $users
 * @property-read Collection<int, PermissionModel> $permissions
 * @property-read Collection<int, AccessLevelModel> $accessLevels
 */
class GroupModel extends Model
{
    use HasFactory;

    protected $table = 'groups';

    protected $fillable = [
        'name', 'alias', 'description', 'status', 'ordering',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * @return BelongsToMany<UserModel, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(UserModel::class, 'group_user', 'group_id', 'user_id');
    }

    /**
     * @return BelongsToMany<PermissionModel, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(PermissionModel::class, 'group_permissions', 'group_id', 'permission_id');
    }

    /**
     * @return BelongsToMany<AccessLevelModel, $this>
     */
    public function accessLevels(): BelongsToMany
    {
        return $this->belongsToMany(AccessLevelModel::class, 'access_level_group', 'group_id', 'access_level_id');
    }

    protected static function newFactory(): GroupFactory
    {
        return GroupFactory::new();
    }
}
