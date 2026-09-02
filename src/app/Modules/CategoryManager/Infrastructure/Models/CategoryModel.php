<?php

namespace App\Modules\CategoryManager\Infrastructure\Models;

use App\Models\Traits\ClearsSitemapCache;
use App\Modules\MaterialManager\Infrastructure\Models\MaterialModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read CategoryModel|null $parent
 * @property-read Collection<int, CategoryModel> $children
 * @property-read Collection<int, MaterialModel> $materials
 * @property-read Collection<int, MaterialModel> $publishedMaterials
 * @property-read Collection<int, MaterialModel> $draftMaterials
 * @property-read Collection<int, MaterialModel> $trashMaterials
 */
class CategoryModel extends Model
{
    use HasFactory, ClearsSitemapCache;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'alias',
        'description',
        'parent_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'parent_id' => 'integer',
        'lft' => 'integer',
        'rgt' => 'integer',
        'depth' => 'integer',
    ];

    /**
     * @return HasMany<MaterialModel, $this>
     */
    public function materials(): HasMany
    {
        return $this->hasMany(MaterialModel::class, 'category_id');
    }

    /**
     * @return BelongsTo<CategoryModel, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'parent_id');
    }

    /**
     * @return HasMany<CategoryModel, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(CategoryModel::class, 'parent_id');
    }

    /**
     * @return HasMany<MaterialModel, $this>
     */
    public function publishedMaterials(): HasMany
    {
        return $this->hasMany(MaterialModel::class, 'category_id')->where('state', 'published');
    }

    /**
     * @return HasMany<MaterialModel, $this>
     */
    public function draftMaterials(): HasMany
    {
        return $this->hasMany(MaterialModel::class, 'category_id')->where('state', 'draft');
    }

    /**
     * @return HasMany<MaterialModel, $this>
     */
    public function trashMaterials(): HasMany
    {
        return $this->hasMany(MaterialModel::class, 'category_id')->where('state', 'trash');
    }
}
