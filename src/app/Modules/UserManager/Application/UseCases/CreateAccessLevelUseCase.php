<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Application\DTO\CreateAccessLevelData;
use App\Modules\UserManager\Domain\Entities\AccessLevel;
use App\Modules\UserManager\Domain\Events\AccessLevelCreated;
use App\Modules\UserManager\Domain\Repositories\AccessLevelRepositoryInterface;

final class CreateAccessLevelUseCase
{
    public function __construct(
        private readonly AccessLevelRepositoryInterface $accessLevels,
    ) {
    }

    public function execute(CreateAccessLevelData $data): AccessLevel
    {
        $accessLevel = $this->accessLevels->create($data);

        event(new AccessLevelCreated($accessLevel));

        return $accessLevel;
    }
}
