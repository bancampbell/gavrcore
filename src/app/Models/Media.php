<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
