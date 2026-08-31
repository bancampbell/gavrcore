<?php

namespace App\Modules\FormBuilder\Infrastructure\Repositories;

use App\Modules\FormBuilder\Domain\Entities\Form;
use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;
use App\Modules\FormBuilder\Domain\ValueObjects\FormFieldCollection;
use App\Modules\FormBuilder\Domain\ValueObjects\FormSettings;
use App\Modules\FormBuilder\Domain\ValueObjects\FormStatus;
use App\Modules\FormBuilder\Infrastructure\Models\FormModel;
use Illuminate\Pagination\LengthAwarePaginator;

class FormRepository implements FormRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = FormModel::query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(int $id): ?Form
    {
        $model = FormModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByAlias(string $alias): ?Form
    {
        $model = FormModel::where('alias', $alias)->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function save(Form $form): Form
    {
        $model = $form->id ? FormModel::find($form->id) : new FormModel();

        if (!$model) {
            $model = new FormModel();
            $model->id = $form->id;
        }

        $model->fill([
            'title' => $form->title,
            'alias' => $form->alias,
            'description' => $form->description,
            'fields' => $form->fields->toArray(),
            'settings' => $form->settings->toArray(),
            'notification_emails' => $form->notificationEmails,
            'status' => $form->status->value(),
            'is_dynamic' => $form->isDynamic,
            'submissions_count' => $form->submissionsCount,
        ]);

        $model->save();
        return $this->toEntity($model);
    }

    public function delete(int $id): bool
    {
        $model = FormModel::find($id);
        return $model ? $model->delete() : false;
    }

    public function listForSelect(): array
    {
        return FormModel::select('id', 'title', 'alias', 'status', 'fields')
            ->orderBy('title')
            ->get()
            ->toArray();
    }

    public function incrementSubmissionsCount(int $formId): void
    {
        FormModel::where('id', $formId)->increment('submissions_count');
    }

    private function toEntity(FormModel $model): Form
    {
        return new Form(
            id: $model->id,
            title: $model->title,
            alias: $model->alias,
            description: $model->description,
            fields: new FormFieldCollection($model->fields ?? []),
            settings: new FormSettings($model->settings ?? []),
            notificationEmails: $model->notification_emails ?? [],
            status: new FormStatus($model->status),
            isDynamic: $model->is_dynamic,
            submissionsCount: $model->submissions_count,
            createdAt: $model->created_at ? new \DateTimeImmutable($model->created_at) : null,
            updatedAt: $model->updated_at ? new \DateTimeImmutable($model->updated_at) : null,
        );
    }
}