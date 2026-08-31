<?php

namespace App\Modules\FormBuilder\Domain\Repositories;

use App\Modules\FormBuilder\Domain\Entities\FormSubmission;
use Illuminate\Pagination\LengthAwarePaginator;

interface FormSubmissionRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function find(int $id): ?FormSubmission;
    public function save(FormSubmission $submission): FormSubmission;
    public function markAsRead(int $id): bool;
    public function delete(int $id): bool;
    public function countUnread(): int;
}