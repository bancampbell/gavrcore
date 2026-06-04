<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuType extends Model
{
    protected $fillable = [
        'title', 'alias', 'description', 'ordering', 'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_type_id');
    }

    public function activeItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_type_id')
            ->where('status', true)
            ->orderBy('ordering');
    }
}
