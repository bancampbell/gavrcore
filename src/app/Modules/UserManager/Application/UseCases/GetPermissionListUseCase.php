<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Domain\Repositories\PermissionRepositoryInterface;
use Illuminate\Support\Collection;

final class GetPermissionListUseCase
{
    public function __construct(
        private readonly PermissionRepositoryInterface $permissions,
    ) {
    }

    /**
     * @return Collection<int, \App\Modules\UserManager\Domain\Entities\Permission>
     */
    public function execute(): Collection
    {
        return $this->permissions->getAll();
    }

    /**
     * @return Collection<string, Collection<int, \App\Modules\UserManager\Domain\Entities\Permission>>
     */
    public function grouped(): Collection
    {
        return $this->permissions->getGrouped();
    }
}
