<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Domain\Repositories\FormSubmissionRepositoryInterface;

class GetUserSubmissionStatsUseCase
{
    public function __construct(protected FormSubmissionRepositoryInterface $repository) {}

    /**
     * @return array{total: int, new: int, in_progress: int, completed: int, rejected: int}
     */
    public function execute(int $userId): array
    {
        return $this->repository->getUserStats($userId);
    }
}
