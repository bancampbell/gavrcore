<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Domain\Events\PermissionDeleted;
use App\Modules\UserManager\Domain\Repositories\PermissionRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\PermissionId;
use DomainException;

final class DeletePermissionUseCase
{
    public function __construct(
        private readonly PermissionRepositoryInterface $permissions,
    ) {
    }

    public function execute(PermissionId $id): void
    {
        $permission = $this->permissions->findById($id);

        if (! $permission) {
            throw new DomainException('Право доступа не найдено');
        }

        $this->permissions->delete($id);

        event(new PermissionDeleted($permission));
    }
}
