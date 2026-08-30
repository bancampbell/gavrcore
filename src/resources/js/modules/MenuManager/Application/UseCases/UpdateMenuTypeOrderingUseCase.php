<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Domain\Repositories\MenuTypeRepositoryInterface;

class UpdateMenuTypeOrderingUseCase
{
    public function __construct(private MenuTypeRepositoryInterface $repository) {}

    public function execute(array $order): bool
    {
        return $this->repository->updateOrdering($order);
    }
}