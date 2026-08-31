<?php

namespace App\Modules\FormBuilder\Domain\Repositories;

use App\Modules\FormBuilder\Domain\Entities\Form;
use Illuminate\Pagination\LengthAwarePaginator;

interface FormRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function findById(int $id): ?Form;
    public function findByAlias(string $alias): ?Form;
    public function save(Form $form): Form;
    public function delete(int $id): bool;
    public function listForSelect(): array;
    public function incrementSubmissionsCount(int $formId): void;
}