<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Domain\Repositories\MenuTypeRepositoryInterface;

class UpdateMenuTypeStatusUseCase
{
    public function __construct(private MenuTypeRepositoryInterface $repository) {}

    public function execute(int $id, bool $status): bool
    {
        $menuType = $this->repository->findById($id);
        if (! $menuType) return false;
        $menuType->status = $status;
        return $menuType->save();
    }
}