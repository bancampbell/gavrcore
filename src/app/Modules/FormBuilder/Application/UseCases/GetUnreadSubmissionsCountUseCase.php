<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Domain\Repositories\FormSubmissionRepositoryInterface;

class GetUnreadSubmissionsCountUseCase
{
    public function __construct(protected FormSubmissionRepositoryInterface $repository) {}

    public function execute(): int
    {
        return $this->repository->countUnread();
    }
}