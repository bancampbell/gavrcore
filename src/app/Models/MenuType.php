<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string $alias
 * @property string|null $description
 * @property int $ordering
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MenuItem> $items
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MenuItem> $activeItems
 */
class MenuType extends Model
{
    protected $fillable = [
        'title', 'alias', 'description', 'ordering', 'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_type_id');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function activeItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_type_id')
            ->where('status', true)
            ->orderBy('ordering');
    }
}
