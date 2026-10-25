<?php

namespace App\Modules\FormBuilder\Infrastructure\Repositories;

use App\Modules\FormBuilder\Domain\Entities\FormSubmission;
use App\Modules\FormBuilder\Domain\Repositories\FormSubmissionRepositoryInterface;
use App\Modules\FormBuilder\Infrastructure\Models\FormSubmissionModel;
use Illuminate\Pagination\LengthAwarePaginator;

class FormSubmissionRepository implements FormSubmissionRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = FormSubmissionModel::with('form');

        if (! empty($filters['form_id'])) {
            $query->where('form_id', $filters['form_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            if ($filters['status'] === 'unread') {
                $query->whereNull('read_at');
            } elseif ($filters['status'] === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('data', 'like', '%'.$search.'%')
                    ->orWhereHas('form', function ($formQuery) use ($search) {
                        $formQuery->where('title', 'like', '%'.$search.'%');
                    });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(int $id): ?FormSubmission
    {
        $model = FormSubmissionModel::with('form')->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function save(FormSubmission $submission): FormSubmission
    {
        $model = $submission->id ? FormSubmissionModel::find($submission->id) : new FormSubmissionModel;

        $model->fill([
            'form_id' => $submission->formId,
            'user_id' => $submission->userId,
            'data' => $submission->data,
            'status' => $submission->status,
            'meta' => $submission->meta,
            'read_at' => $submission->readAt,
        ]);

        $model->save();

        return $this->toEntity($model);
    }

    public function markAsRead(int $id): bool
    {
        $model = FormSubmissionModel::find($id);
        if (! $model) {
            return false;
        }

        if ($model->read_at === null) {
            $model->read_at = now();

            return $model->save();
        }

        return true;
    }

    public function delete(int $id): bool
    {
        $model = FormSubmissionModel::find($id);

        return $model ? $model->delete() : false;
    }

    public function countUnread(): int
    {
        return FormSubmissionModel::whereNull('read_at')->count();
    }

    public function getUserStats(int $userId): array
    {
        $rows = FormSubmissionModel::select('status')
            ->selectRaw('COUNT(*) as count')
            ->where('user_id', $userId)
            ->groupBy('status')
            ->pluck('count', 'status');

        $new = (int) ($rows[FormSubmission::STATUS_NEW] ?? 0);
        $inProgress = (int) ($rows[FormSubmission::STATUS_IN_PROGRESS] ?? 0);
        $completed = (int) ($rows[FormSubmission::STATUS_COMPLETED] ?? 0);
        $rejected = (int) ($rows[FormSubmission::STATUS_REJECTED] ?? 0);

        return [
            'total' => $new + $inProgress + $completed + $rejected,
            'new' => $new,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'rejected' => $rejected,
        ];
    }

    private function toEntity(FormSubmissionModel $model): FormSubmission
    {
        return new FormSubmission(
            id: $model->id,
            formId: $model->form_id,
            userId: $model->user_id,
            data: $model->data,
            status: $model->status ?? FormSubmission::STATUS_NEW,
            meta: $model->meta,
            readAt: $model->read_at ? new \DateTimeImmutable($model->read_at) : null,
            createdAt: $model->created_at ? new \DateTimeImmutable($model->created_at) : null,
            updatedAt: $model->updated_at ? new \DateTimeImmutable($model->updated_at) : null,
        );
    }
}
