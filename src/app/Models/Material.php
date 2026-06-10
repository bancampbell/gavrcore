<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AccessLevel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @property int $id
 * @property string $title
 * @property string $alias
 * @property string|null $content
 * @property int $category_id
 * @property int $user_id
 * @property string $state
 * @property string $access
 * @property int $views
 * @property string|null $published_at
 * @property bool $featured
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Category|null $category
 * @property-read User|null $user
 * @property-read AccessLevel|null $accessLevel
 * @method static \Illuminate\Database\Eloquent\Factories\Factory<static> factory()
 */
class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'alias',
        'content',
        'category_id',
        'user_id',
        'state',
        'access',
        'views',
        'published_at',
        'featured'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'views' => 'integer',
        'featured' => 'boolean',
    ];

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<AccessLevel, $this>
     */
    public function accessLevel(): BelongsTo
    {
        return $this->belongsTo(AccessLevel::class, 'access_level_id');
    }
}
