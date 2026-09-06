<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Application\DTO\CreateGroupData;
use App\Modules\UserManager\Domain\Entities\Group;
use App\Modules\UserManager\Domain\Events\GroupCreated;
use App\Modules\UserManager\Domain\Repositories\GroupRepositoryInterface;

final class CreateGroupUseCase
{
    public function __construct(
        private readonly GroupRepositoryInterface $groups,
    ) {
    }

    public function execute(CreateGroupData $data): Group
    {
        $group = $this->groups->create($data);

        event(new GroupCreated($group));

        return $group;
    }
}
