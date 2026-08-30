<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Domain\Repositories\MenuTypeRepositoryInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;

class GetMenuTypeByIdUseCase
{
    public function __construct(private MenuTypeRepositoryInterface $repository) {}

    public function execute(int $id): ?MenuTypeModel
    {
        return $this->repository->findById($id);
    }
}