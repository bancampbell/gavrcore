<?php

namespace App\Modules\UserManager\Infrastructure\Http\Resources;

use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Публичное представление пользователя для API-аутентификации.
 *
 * @mixin UserModel
 */
class AuthUserResource extends JsonResource
{
    /**
     * @return array<string, int|string|null>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
