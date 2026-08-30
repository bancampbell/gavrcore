<?php

namespace App\Modules\MenuManager\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuTreeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'title' => $this['title'],
            'link_type' => $this['link_type'],
            'link_value' => $this['link_value'],
            'target' => $this['target'],
            'children' => self::collection($this['children'] ?? []),
        ];
    }
}