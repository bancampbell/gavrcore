<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Application\DTO\UpdatePermissionData;
use App\Modules\UserManager\Domain\Entities\Permission;
use App\Modules\UserManager\Domain\Events\PermissionUpdated;
use App\Modules\UserManager\Domain\Repositories\PermissionRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\PermissionId;

final class UpdatePermissionUseCase
{
    public function __construct(
        private readonly PermissionRepositoryInterface $permissions,
    ) {
    }

    public function execute(PermissionId $id, UpdatePermissionData $data): Permission
    {
        $permission = $this->permissions->update($id, $data);

        event(new PermissionUpdated($permission));

        return $permission;
    }
}
