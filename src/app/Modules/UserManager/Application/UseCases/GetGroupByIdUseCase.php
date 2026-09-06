<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Domain\Entities\Group;
use App\Modules\UserManager\Domain\Repositories\GroupRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\GroupId;

final class GetGroupByIdUseCase
{
    public function __construct(
        private readonly GroupRepositoryInterface $groups,
    ) {
    }

    public function execute(GroupId $id): ?Group
    {
        return $this->groups->findById($id);
    }
}
