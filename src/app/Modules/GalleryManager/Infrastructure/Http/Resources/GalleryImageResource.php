<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'gallery_id' => $this->gallery_id,
            'image_path' => $this->image_path,
            'title' => $this->title,
            'description' => $this->description,
            'alt_text' => $this->alt_text,
            'link' => $this->link,
            'ordering' => $this->ordering,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
