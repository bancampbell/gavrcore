<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Domain\Events\FormDeleted;
use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;

class DeleteFormUseCase
{
    public function __construct(protected FormRepositoryInterface $repository) {}

    public function execute(int $id): bool
    {
        $deleted = $this->repository->delete($id);

        if ($deleted) {
            event(new FormDeleted($id));
        }

        return $deleted;
    }
}
