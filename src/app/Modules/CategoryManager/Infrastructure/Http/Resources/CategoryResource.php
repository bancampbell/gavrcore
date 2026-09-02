<?php

namespace App\Modules\CategoryManager\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'alias' => $this->alias,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'depth' => $this->depth,
            'lft' => $this->lft,
            'rgt' => $this->rgt,
            'is_active' => $this->is_active,
            'published_count' => $this->when(isset($this->published_count), $this->published_count),
            'draft_count' => $this->when(isset($this->draft_count), $this->draft_count),
            'trash_count' => $this->when(isset($this->trash_count), $this->trash_count),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
