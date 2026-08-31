<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Domain\Entities\FormSubmission;
use App\Modules\FormBuilder\Domain\Repositories\FormSubmissionRepositoryInterface;

class ShowSubmissionUseCase
{
    public function __construct(protected FormSubmissionRepositoryInterface $repository) {}

    public function execute(int $id): ?FormSubmission
    {
        $submission = $this->repository->find($id);
        if ($submission && !$submission->isRead()) {
            $this->repository->markAsRead($id);
            $submission->markAsRead();
        }
        return $submission;
    }
}