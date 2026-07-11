<?php

namespace App\Repositories;

use App\Contracts\FormSubmissionRepositoryInterface;
use App\Models\FormSubmission;
use Illuminate\Pagination\LengthAwarePaginator;

class FormSubmissionRepository implements FormSubmissionRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = FormSubmission::with('form');

        // Фильтр по форме
        if (!empty($filters['form_id'])) {
            $query->where('form_id', $filters['form_id']);
        }

        // Фильтр по статусу (прочитано/не прочитано)
        if (isset($filters['status']) && $filters['status'] !== '') {
            if ($filters['status'] === 'unread') {
                $query->whereNull('read_at');
            } elseif ($filters['status'] === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        // Поиск по данным формы
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('data', 'like', '%' . $search . '%')
                    ->orWhereHas('form', function ($formQuery) use ($search) {
                        $formQuery->where('title', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(int $id): ?FormSubmission
    {
        return FormSubmission::with('form')->find($id);
    }

    public function markAsRead(int $id): bool
    {
        $submission = FormSubmission::find($id);

        if (!$submission) {
            return false;
        }

        if ($submission->read_at === null) {
            $submission->read_at = now();
            return $submission->save();
        }

        return true;
    }

    public function delete(int $id): bool
    {
        $submission = FormSubmission::find($id);

        if (!$submission) {
            return false;
        }

        return $submission->delete();
    }

    public function countUnread(): int
    {
        return FormSubmission::whereNull('read_at')->count();
    }
}
