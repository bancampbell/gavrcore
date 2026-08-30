<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Domain\Repositories\MenuTypeRepositoryInterface;

class DeleteMenuTypeUseCase
{
    public function __construct(private MenuTypeRepositoryInterface $repository) {}

    public function execute(int $id): bool
    {
        return $this->repository->delete($id);
    }
}