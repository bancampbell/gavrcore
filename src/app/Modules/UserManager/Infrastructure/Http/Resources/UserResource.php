<?php

namespace App\Modules\UserManager\Infrastructure\Http\Resources;

use App\Modules\UserManager\Domain\Entities\Group;
use App\Modules\UserManager\Domain\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'id' => $user->id?->value,
            'name' => $user->name,
            'username' => $user->username->value,
            'email' => $user->email->value,
            'blocked' => $user->blocked,
            'activated' => $user->activated,
            'last_login_at' => $user->lastLoginAt,
            'last_login_ip' => $user->lastLoginIp,
            'created_at' => $user->createdAt,
            'groups' => array_map(fn (Group $group) => [
                'id' => $group->id?->value,
                'name' => $group->name,
            ], $user->groups),
        ];
    }
}
