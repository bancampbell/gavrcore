<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Domain\Entities\AccessLevel;
use App\Modules\UserManager\Domain\Repositories\AccessLevelRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\AccessLevelId;

final class GetAccessLevelByIdUseCase
{
    public function __construct(
        private readonly AccessLevelRepositoryInterface $accessLevels,
    ) {
    }

    public function execute(AccessLevelId $id): ?AccessLevel
    {
        return $this->accessLevels->findById($id);
    }
}
