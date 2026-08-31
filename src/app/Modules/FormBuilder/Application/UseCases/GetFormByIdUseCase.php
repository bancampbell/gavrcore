<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Domain\Entities\Form;
use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;

class GetFormByIdUseCase
{
    public function __construct(protected FormRepositoryInterface $repository) {}

    public function execute(int $id): ?Form
    {
        return $this->repository->findById($id);
    }
}