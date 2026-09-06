<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Domain\Repositories\GroupRepositoryInterface;
use Illuminate\Support\Collection;

final class GetAllGroupsUseCase
{
    public function __construct(
        private readonly GroupRepositoryInterface $groups,
    ) {
    }

    public function execute(): Collection
    {
        return $this->groups->getAll();
    }
}
