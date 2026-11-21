<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Domain\Events\SubmissionDeleted;
use App\Modules\FormBuilder\Domain\Repositories\FormSubmissionRepositoryInterface;

class DeleteSubmissionUseCase
{
    public function __construct(protected FormSubmissionRepositoryInterface $repository) {}

    public function execute(int $id, int $actorId): bool
    {
        $submission = $this->repository->find($id);

        if (! $submission) {
            return false;
        }

        $deleted = $this->repository->delete($id);

        if ($deleted) {
            event(new SubmissionDeleted($submission, $actorId));
        }

        return $deleted;
    }
}
