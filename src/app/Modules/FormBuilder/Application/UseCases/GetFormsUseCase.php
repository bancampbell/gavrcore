<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Application\DTO\FormFiltersData;
use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class GetFormsUseCase
{
    public function __construct(protected FormRepositoryInterface $repository) {}

    public function execute(FormFiltersData $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->getPaginated([
            'search' => $filters->search,
            'status' => $filters->status,
        ], $perPage);
    }
}