<?php

namespace App\Services;

use App\Models\Form;
use Illuminate\Pagination\LengthAwarePaginator;

class FormService
{
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Form::query();

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%')
                ->orWhere('description', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Form
    {
        return Form::create($data);
    }

    public function update(Form $form, array $data): Form
    {
        $form->update($data);
        return $form->refresh(); // <--- ИЗМЕНЕНО: fresh() на refresh()
    }

    public function delete(Form $form): bool
    {
        return $form->delete();
    }
}
