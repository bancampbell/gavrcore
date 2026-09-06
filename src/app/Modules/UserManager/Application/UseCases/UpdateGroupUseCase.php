<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Application\DTO\UpdateGroupData;
use App\Modules\UserManager\Domain\Entities\Group;
use App\Modules\UserManager\Domain\Events\GroupUpdated;
use App\Modules\UserManager\Domain\Repositories\GroupRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\GroupId;
use Illuminate\Validation\ValidationException;

final class UpdateGroupUseCase
{
    public function __construct(
        private readonly GroupRepositoryInterface $groups,
    ) {
    }

    public function execute(GroupId $id, UpdateGroupData $data): Group
    {
        $current = $this->groups->findById($id);

        if (! $current) {
            throw ValidationException::withMessages([
                'group' => ['Группа не найдена'],
            ]);
        }

        // Системная группа идентифицируется по алиасу. Смена алиаса обнуляет
        // защиту от удаления (DeleteGroupUseCase смотрит на isSystem()), и
        // группа administrators становится удаляемой — каскад стирает права
        // всех админов. Поэтому алиас системной группы неизменен.
        if ($current->isSystem() && $current->alias->value !== $data->alias) {
            throw ValidationException::withMessages([
                'alias' => ['Алиас системной группы нельзя изменить'],
            ]);
        }

        $group = $this->groups->update($id, $data);

        event(new GroupUpdated($group));

        return $group;
    }
}
