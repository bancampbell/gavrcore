<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'settings' => $this->settings,
            'status' => $this->status,
            'ordering' => $this->ordering,
            'images' => GalleryImageResource::collection($this->whenLoaded('images')),
            'images_count' => $this->whenCounted('images'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
