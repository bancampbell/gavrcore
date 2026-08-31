<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Application\DTO\SubmissionFiltersData;
use App\Modules\FormBuilder\Domain\Repositories\FormSubmissionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class GetSubmissionsUseCase
{
    public function __construct(protected FormSubmissionRepositoryInterface $repository) {}

    public function execute(SubmissionFiltersData $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->getPaginated([
            'search' => $filters->search,
            'status' => $filters->status,
            'form_id' => $filters->formId,
        ], $perPage);
    }
}