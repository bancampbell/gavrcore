<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;

class DeleteFormUseCase
{
    public function __construct(protected FormRepositoryInterface $repository) {}

    public function execute(int $id): bool
    {
        return $this->repository->delete($id);
    }
}