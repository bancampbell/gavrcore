<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $path
 * @property string|null $type
 * @property int|null $size
 * @property string|null $mime_type
 * @property int|null $parent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'name',
        'path',
        'type',
        'size',
        'mime_type',
        'parent_id'
    ];
}
