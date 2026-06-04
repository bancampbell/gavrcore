<?php

namespace App\Http\Resources\Admin\Menu;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_type_id' => $this->menu_type_id,
            'parent_id' => $this->parent_id,
            'title' => $this->title,
            'alias' => $this->alias,
            'link_type' => $this->link_type,
            'link_value' => $this->link_value,
            'target' => $this->target,
            'ordering' => $this->ordering,
            'status' => (bool) $this->status,
            'access' => $this->access,
            'language' => $this->language,
            'menu_type' => $this->whenLoaded('menuType', function () {
                return [
                    'title' => $this->menuType->title,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
