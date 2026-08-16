<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryImageModel extends Model
{
    use HasFactory;

    protected $table = 'gallery_images';

    protected $fillable = [
        'gallery_id',
        'image_path',
        'title',
        'description',
        'alt_text',
        'link',
        'ordering',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(GalleryModel::class, 'gallery_id');
    }
}
