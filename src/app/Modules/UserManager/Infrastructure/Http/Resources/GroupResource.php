<?php

namespace App\Modules\UserManager\Infrastructure\Http\Resources;

use App\Modules\UserManager\Domain\Entities\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Group
 */
class GroupResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Group $group */
        $group = $this->resource;

        return [
            'id' => $group->id?->value,
            'name' => $group->name,
            'alias' => $group->alias->value,
            'description' => $group->description,
            'status' => $group->status,
            'ordering' => $group->ordering,
            'permissions' => $group->permissionIds,
            'created_at' => $group->createdAt,
        ];
    }
}
