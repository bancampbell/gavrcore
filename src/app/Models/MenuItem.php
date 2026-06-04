<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_type_id', 'parent_id', 'title', 'alias', 'link_type',
        'link_value', 'target', 'ordering', 'status', 'access', 'language'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function menuType(): BelongsTo
    {
        return $this->belongsTo(MenuType::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id');
    }
}
