<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;

class GetActiveFormsUseCase
{
    public function __construct(protected FormRepositoryInterface $repository) {}

    /**
     * Опубликованные формы, проиндексированные по id (для рендера шорткодов).
     *
     * @return array<int, array<string, mixed>>
     */
    public function execute(): array
    {
        return $this->repository->listActiveForSelect();
    }
}
