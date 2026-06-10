<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $menu_type_id
 * @property int|null $parent_id
 * @property string $title
 * @property string $alias
 * @property string $link_type
 * @property string|null $link_value
 * @property string $target
 * @property int $ordering
 * @property bool $status
 * @property string $access
 * @property string $language
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read MenuType|null $menuType
 * @property-read MenuItem|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MenuItem> $children
 */
class MenuItem extends Model
{
    protected $fillable = [
        'menu_type_id', 'parent_id', 'title', 'alias', 'link_type',
        'link_value', 'target', 'ordering', 'status', 'access', 'language'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * @return BelongsTo<MenuType, $this>
     */
    public function menuType(): BelongsTo
    {
        return $this->belongsTo(MenuType::class);
    }

    /**
     * @return BelongsTo<MenuItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id');
    }
}
