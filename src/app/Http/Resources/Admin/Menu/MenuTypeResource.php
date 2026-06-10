<?php

namespace App\Http\Resources\Admin\Menu;

use App\Models\MenuType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $title
 * @property string $alias
 * @property string|null $description
 * @property int $ordering
 * @property bool $status
 * @property int $items_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class MenuTypeResource extends JsonResource
{
    /**
     * @return array<string, int|string|bool|null>
     */
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
