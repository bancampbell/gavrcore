<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;
use App\Modules\FormBuilder\Domain\ValueObjects\FormStatus;

class UpdateFormStatusUseCase
{
    public function __construct(protected FormRepositoryInterface $repository) {}

    public function execute(int $id, bool $status): bool
    {
        $form = $this->repository->findById($id);
        if (!$form) return false;

        $form->update(['status' => new FormStatus($status)]);
        $this->repository->save($form);
        return true;
    }
}