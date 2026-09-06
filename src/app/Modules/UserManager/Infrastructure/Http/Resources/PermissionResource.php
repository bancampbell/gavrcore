<?php

namespace App\Modules\UserManager\Infrastructure\Http\Resources;

use App\Modules\UserManager\Domain\Entities\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Permission
 */
class PermissionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Permission $permission */
        $permission = $this->resource;

        return [
            'id' => $permission->id?->value,
            'name' => $permission->name,
            'key' => $permission->key,
            'group' => $permission->group,
            'description' => $permission->description,
        ];
    }
}
