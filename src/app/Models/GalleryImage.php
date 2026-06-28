<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryImage extends Model
{
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
        return $this->belongsTo(Gallery::class);
    }
}
