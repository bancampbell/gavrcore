<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Application\DTO\AccessLevelFiltersData;
use App\Modules\UserManager\Domain\Repositories\AccessLevelRepositoryInterface;
use Illuminate\Support\Collection;

final class GetAccessLevelListUseCase
{
    public function __construct(
        private readonly AccessLevelRepositoryInterface $accessLevels,
    ) {
    }

    public function execute(AccessLevelFiltersData $filters): Collection
    {
        return $this->accessLevels->getAll($filters);
    }
}
