<?php

namespace App\Services;

use App\Contracts\FormSubmissionRepositoryInterface;
use App\Models\FormSubmission;
use Illuminate\Pagination\LengthAwarePaginator;

class FormSubmissionService
{
    public function __construct(
        protected FormSubmissionRepositoryInterface $repository
    ) {
    }

    /**
     * Получить список с пагинацией и фильтрацией
     */
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    /**
     * Найти по ID
     */
    public function find(int $id): ?FormSubmission
    {
        return $this->repository->find($id);
    }

    /**
     * Отметить как прочитанное
     */
    public function markAsRead(int $id): bool
    {
        return $this->repository->markAsRead($id);
    }

    /**
     * Удалить
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Получить количество непрочитанных
     */
    public function countUnread(): int
    {
        return $this->repository->countUnread();
    }
}
