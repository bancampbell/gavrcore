<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Domain\Repositories\AccessLevelRepositoryInterface;

final class UpdateAccessLevelOrderingUseCase
{
    public function __construct(
        private readonly AccessLevelRepositoryInterface $accessLevels,
    ) {
    }

    /**
     * @param  array<int, array{id: int, ordering: int}>  $order
     */
    public function execute(array $order): bool
    {
        return $this->accessLevels->updateOrdering($order);
    }
}
