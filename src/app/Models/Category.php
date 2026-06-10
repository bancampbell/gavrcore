<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @property int $id
 * @property string $name
 * @property string $alias
 * @property string|null $description
 * @property int|null $parent_id
 * @property int $lft
 * @property int $rgt
 * @property int $depth
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Material> $materials
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Material> $publishedMaterials
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Material> $draftMaterials
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Material> $trashMaterials
 * @method static \Illuminate\Database\Eloquent\Factories\Factory<static> factory()
 */
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

    /**
     * @return HasMany<Material, $this>
     */
    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<Material, $this>
     */
    public function publishedMaterials(): HasMany
    {
        return $this->hasMany(Material::class)->where('state', 'published');
    }

    /**
     * @return HasMany<Material, $this>
     */
    public function draftMaterials(): HasMany
    {
        return $this->hasMany(Material::class)->where('state', 'draft');
    }

    /**
     * @return HasMany<Material, $this>
     */
    public function trashMaterials(): HasMany
    {
        return $this->hasMany(Material::class)->where('state', 'trash');
    }
}
