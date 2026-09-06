<?php

namespace App\Modules\UserManager\Infrastructure\Http\Resources;

use App\Modules\UserManager\Domain\Entities\AccessLevel;
use App\Modules\UserManager\Domain\Entities\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AccessLevel
 */
class AccessLevelResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var AccessLevel $accessLevel */
        $accessLevel = $this->resource;

        return [
            'id' => $accessLevel->id?->value,
            'title' => $accessLevel->title,
            'alias' => $accessLevel->alias->value,
            'description' => $accessLevel->description,
            'ordering' => $accessLevel->ordering,
            'status' => $accessLevel->status,
            'groups' => array_map(fn (Group $group) => [
                'id' => $group->id?->value,
                'name' => $group->name,
            ], $accessLevel->groups),
        ];
    }
}
