<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Domain\Events\AccessLevelStatusChanged;
use App\Modules\UserManager\Domain\Repositories\AccessLevelRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\AccessLevelId;
use DomainException;

final class UpdateAccessLevelStatusUseCase
{
    public function __construct(
        private readonly AccessLevelRepositoryInterface $accessLevels,
    ) {
    }

    public function execute(AccessLevelId $id, bool $status): bool
    {
        $accessLevel = $this->accessLevels->findById($id);

        if (! $accessLevel) {
            throw new DomainException('Уровень доступа не найден');
        }

        $result = $this->accessLevels->updateStatus($id, $status);

        event(new AccessLevelStatusChanged($accessLevel, $status));

        return $result;
    }
}
