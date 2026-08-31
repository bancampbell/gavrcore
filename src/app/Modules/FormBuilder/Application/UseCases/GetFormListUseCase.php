<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;

class GetFormListUseCase
{
    public function __construct(protected FormRepositoryInterface $repository) {}

    public function execute(): array
    {
        return $this->repository->listForSelect();
    }
}