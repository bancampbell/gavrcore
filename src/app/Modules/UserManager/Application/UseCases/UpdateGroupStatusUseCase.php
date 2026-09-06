<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Domain\Events\GroupStatusChanged;
use App\Modules\UserManager\Domain\Repositories\GroupRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\GroupId;
use DomainException;

final class UpdateGroupStatusUseCase
{
    public function __construct(
        private readonly GroupRepositoryInterface $groups,
    ) {
    }

    public function execute(GroupId $id, bool $status): bool
    {
        $group = $this->groups->findById($id);

        if (! $group) {
            throw new DomainException('Группа не найдена');
        }

        $result = $this->groups->updateStatus($id, $status);

        event(new GroupStatusChanged($group, $status));

        return $result;
    }
}
