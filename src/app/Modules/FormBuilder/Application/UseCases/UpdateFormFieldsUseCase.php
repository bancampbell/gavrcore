<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Application\DTO\UpdateFormFieldsData;
use App\Modules\FormBuilder\Domain\Entities\Form;
use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;
use App\Modules\FormBuilder\Domain\ValueObjects\FormFieldCollection;

class UpdateFormFieldsUseCase
{
    public function __construct(protected FormRepositoryInterface $repository) {}

    public function execute(UpdateFormFieldsData $data): ?Form
    {
        $form = $this->repository->findById($data->formId);
        if (!$form) return null;

        $form->update(['fields' => new FormFieldCollection($data->fields)]);
        return $this->repository->save($form);
    }
}