<?php

namespace App\Modules\CategoryManager\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryTreeResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'name' => $this['name'],
            'alias' => $this['alias'],
            'description' => $this['description'],
            'depth' => $this['depth'],
            'lft' => $this['lft'],
            'rgt' => $this['rgt'],
            'is_active' => $this['is_active'],
            'created_at' => $this['created_at'],
            'updated_at' => $this['updated_at'],
            'children' => $this['children'] ?? [],
        ];
    }
}
