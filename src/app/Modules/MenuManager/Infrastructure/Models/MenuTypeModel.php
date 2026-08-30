<?php

namespace App\Modules\MenuManager\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuTypeModel extends Model
{
    protected $table = 'menu_types';
    protected $fillable = ['title', 'alias', 'description', 'ordering', 'status'];
    protected $casts = ['status' => 'boolean'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItemModel::class, 'menu_type_id');
    }

    public function activeItems(): HasMany
    {
        return $this->hasMany(MenuItemModel::class, 'menu_type_id')
            ->where('status', true)
            ->orderBy('ordering');
    }
}