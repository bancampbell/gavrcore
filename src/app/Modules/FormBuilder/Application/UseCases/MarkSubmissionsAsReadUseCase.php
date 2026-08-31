<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Domain\Repositories\FormSubmissionRepositoryInterface;

class MarkSubmissionsAsReadUseCase
{
    public function __construct(protected FormSubmissionRepositoryInterface $repository) {}

    public function execute(array $ids): int
    {
        $count = 0;
        foreach ($ids as $id) {
            if ($this->repository->markAsRead($id)) $count++;
        }
        return $count;
    }
}