<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Domain\Events\GroupDeleted;
use App\Modules\UserManager\Domain\Repositories\GroupRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\GroupId;
use DomainException;

final class DeleteGroupUseCase
{
    public function __construct(
        private readonly GroupRepositoryInterface $groups,
    ) {
    }

    public function execute(GroupId $id): void
    {
        $group = $this->groups->findById($id);

        if (! $group) {
            throw new DomainException('Группа не найдена');
        }

        // Системная группа: удаление каскадно стирает group_permissions
        // и group_user — все её участники, включая администраторов,
        // мгновенно теряют права. Восстановление только через БД.
        if ($group->isSystem()) {
            throw new DomainException('Системную группу нельзя удалить');
        }

        $this->groups->delete($id);

        event(new GroupDeleted($group));
    }
}
