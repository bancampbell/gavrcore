<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Domain\Events\AccessLevelDeleted;
use App\Modules\UserManager\Domain\Repositories\AccessLevelRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\AccessLevelId;
use DomainException;
use Illuminate\Validation\ValidationException;

final class DeleteAccessLevelUseCase
{
    public function __construct(
        private readonly AccessLevelRepositoryInterface $accessLevels,
    ) {
    }

    public function execute(AccessLevelId $id): void
    {
        $accessLevel = $this->accessLevels->findById($id);

        if (! $accessLevel) {
            throw new DomainException('Уровень доступа не найден');
        }

        // Стандартный уровень (public, guest, registered, special,
        // super-users): удаление каскадно стирает access_level_group, и
        // публичная часть теряет уровень, к которому резолвит доступ.
        // Восстановление только через БД. По аналогии с DeleteGroupUseCase.
        if ($accessLevel->isSystem()) {
            throw ValidationException::withMessages([
                'access_level' => ['Стандартный уровень доступа нельзя удалить'],
            ]);
        }

        $this->accessLevels->delete($id);

        event(new AccessLevelDeleted($accessLevel));
    }
}
