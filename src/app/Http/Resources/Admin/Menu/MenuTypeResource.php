<?php

namespace App\Http\Resources\Admin\Menu;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'alias' => $this->alias,
            'description' => $this->description,
            'ordering' => $this->ordering,
            'status' => (bool) $this->status,
            'items_count' => $this->items_count ?? 0,
        ];
    }
}
