<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'alias',
        'description',
        'parent_id',
        'lft',
        'rgt',
        'depth',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'parent_id' => 'integer',
        'lft' => 'integer',
        'rgt' => 'integer',
        'depth' => 'integer',
    ];

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function publishedMaterials()
    {
        return $this->hasMany(Material::class)->where('state', 'published');
    }

    public function draftMaterials()
    {
        return $this->hasMany(Material::class)->where('state', 'draft');
    }

    public function trashMaterials()
    {
        return $this->hasMany(Material::class)->where('state', 'trash');
    }
}
