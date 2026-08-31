<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Application\DTO\UpdateFormData;
use App\Modules\FormBuilder\Domain\Entities\Form;
use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;
use App\Modules\FormBuilder\Domain\Services\SlugGeneratorInterface;
use App\Modules\FormBuilder\Domain\ValueObjects\FormFieldCollection;
use App\Modules\FormBuilder\Domain\ValueObjects\FormSettings;
use App\Modules\FormBuilder\Domain\ValueObjects\FormStatus;

class UpdateFormUseCase
{
    public function __construct(
        protected FormRepositoryInterface $repository,
        protected SlugGeneratorInterface $slugGenerator,
    ) {}

    public function execute(int $id, UpdateFormData $data): ?Form
    {
        $form = $this->repository->findById($id);
        if (!$form) return null;

        $updateData = [
            'title' => $data->title,
            'description' => $data->description,
            'notification_emails' => $data->notificationEmails,
            'status' => $data->status,
        ];

        if ($data->alias !== null) {
            $updateData['alias'] = $data->alias;
        } elseif (empty($form->alias) && !empty($data->title)) {
            $baseAlias = $this->slugGenerator->generate($data->title);
            if (trim($baseAlias) === '') {
                $baseAlias = 'form';
            }

            $alias = $baseAlias;
            $suffix = 1;
            while ($this->repository->findByAlias($alias) !== null) {
                $alias = $baseAlias . '-' . $suffix++;
            }

            $updateData['alias'] = $alias;
        }

        if ($data->fields !== null) {
            $updateData['fields'] = new FormFieldCollection($data->fields);
        }
        if ($data->settings !== null) {
            $updateData['settings'] = new FormSettings($data->settings);
        }
        if ($data->isDynamic !== null) {
            $updateData['is_dynamic'] = $data->isDynamic;
        }

        $form->update($updateData);
        return $this->repository->save($form);
    }
}