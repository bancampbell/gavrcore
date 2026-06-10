<?php

namespace App\Http\Resources\Admin\Menu;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $menu_type_id
 * @property int|null $parent_id
 * @property string $title
 * @property string $alias
 * @property string $link_type
 * @property string $link_value
 * @property string $target
 * @property int $ordering
 * @property bool $status
 * @property int $access
 * @property string $language
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MenuType $menuType
 */
class MenuItemResource extends JsonResource
{
    /**
     * @return array<string, int|string|bool|null>
     */
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
