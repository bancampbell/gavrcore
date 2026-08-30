<?php

namespace App\Modules\MenuManager\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItemModel extends Model
{
    protected $table = 'menu_items';
    protected $fillable = [
        'menu_type_id', 'parent_id', 'title', 'alias', 'link_type',
        'link_value', 'target', 'ordering', 'status', 'access', 'language',
    ];
    protected $casts = ['status' => 'boolean'];

    public function menuType(): BelongsTo
    {
        return $this->belongsTo(MenuTypeModel::class, 'menu_type_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItemModel::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItemModel::class, 'parent_id');
    }
}