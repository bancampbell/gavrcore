<?php

namespace App\Contracts;

use App\Models\FormSubmission;
use Illuminate\Pagination\LengthAwarePaginator;

interface FormSubmissionRepositoryInterface
{
    /**
     * Получить список с пагинацией и фильтрацией
     */
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Найти по ID
     */
    public function find(int $id): ?FormSubmission;

    /**
     * Отметить как прочитанное
     */
    public function markAsRead(int $id): bool;

    /**
     * Удалить
     */
    public function delete(int $id): bool;

    /**
     * Получить количество непрочитанных
     */
    public function countUnread(): int;
}
