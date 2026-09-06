<?php

namespace App\Modules\UserManager\Domain\Entities;

use App\Modules\UserManager\Domain\ValueObjects\PermissionId;

final class Permission
{
    public function __construct(
        public readonly ?PermissionId $id,
        public readonly string $name,
        public readonly string $key,
        public readonly ?string $group,
        public readonly ?string $description,
    ) {
    }
}
