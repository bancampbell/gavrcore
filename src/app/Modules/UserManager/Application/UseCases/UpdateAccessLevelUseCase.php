<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Application\DTO\UpdateAccessLevelData;
use App\Modules\UserManager\Domain\Entities\AccessLevel;
use App\Modules\UserManager\Domain\Events\AccessLevelUpdated;
use App\Modules\UserManager\Domain\Repositories\AccessLevelRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\AccessLevelId;
use DomainException;
use Illuminate\Validation\ValidationException;

final class UpdateAccessLevelUseCase
{
    public function __construct(
        private readonly AccessLevelRepositoryInterface $accessLevels,
    ) {
    }

    public function execute(AccessLevelId $id, UpdateAccessLevelData $data): AccessLevel
    {
        $current = $this->accessLevels->findById($id);

        if (! $current) {
            throw new DomainException('Уровень доступа не найден');
        }

        // Стандартный уровень идентифицируется по алиасу: публичная часть
        // резолвит доступ по алиасу (public, guest, registered и т.д.).
        // Смена алиаса молча разрывает эти привязки — запрещена,
        // по аналогии с защитой системных групп в UpdateGroupUseCase.
        if ($current->isSystem() && $current->alias->value !== $data->alias) {
            throw ValidationException::withMessages([
                'alias' => ['Алиас стандартного уровня доступа нельзя изменить'],
            ]);
        }

        $accessLevel = $this->accessLevels->update($id, $data);

        event(new AccessLevelUpdated($accessLevel));

        return $accessLevel;
    }
}
