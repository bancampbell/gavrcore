<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Application\DTO\CreatePermissionData;
use App\Modules\UserManager\Domain\Entities\Permission;
use App\Modules\UserManager\Domain\Events\PermissionCreated;
use App\Modules\UserManager\Domain\Repositories\PermissionRepositoryInterface;

final class CreatePermissionUseCase
{
    public function __construct(
        private readonly PermissionRepositoryInterface $permissions,
    ) {
    }

    public function execute(CreatePermissionData $data): Permission
    {
        $permission = $this->permissions->create($data);

        event(new PermissionCreated($permission));

        return $permission;
    }
}
