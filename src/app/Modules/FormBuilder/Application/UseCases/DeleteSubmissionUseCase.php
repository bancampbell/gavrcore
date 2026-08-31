<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Domain\Repositories\FormSubmissionRepositoryInterface;

class DeleteSubmissionUseCase
{
    public function __construct(protected FormSubmissionRepositoryInterface $repository) {}

    public function execute(int $id): bool
    {
        return $this->repository->delete($id);
    }
}