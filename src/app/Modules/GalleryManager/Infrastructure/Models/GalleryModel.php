<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryModel extends Model
{
    use HasFactory;

    protected $table = 'galleries';

    protected $fillable = [
        'title',
        'type',
        'settings',
        'status',
        'ordering',
    ];

    protected $casts = [
        'settings' => 'array',
        'status' => 'boolean',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImageModel::class, 'gallery_id')->orderBy('ordering');
    }

    public function scopePublished($query)
    {
        return $query->where('status', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
