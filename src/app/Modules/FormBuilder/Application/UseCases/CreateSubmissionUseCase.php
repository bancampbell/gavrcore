<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Application\DTO\CreateSubmissionData;
use App\Modules\FormBuilder\Domain\Entities\FormSubmission;
use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;
use App\Modules\FormBuilder\Domain\Repositories\FormSubmissionRepositoryInterface;
use App\Modules\FormBuilder\Domain\Services\ClockInterface;

class CreateSubmissionUseCase
{
    public function __construct(
        protected FormRepositoryInterface $formRepository,
        protected FormSubmissionRepositoryInterface $submissionRepository,
        protected ClockInterface $clock,
    ) {}

    public function execute(CreateSubmissionData $data): ?FormSubmission
    {
        $form = $this->formRepository->findById($data->formId);
        if (!$form || !$form->status->isPublished()) return null;

        $submission = new FormSubmission(
            id: null,
            formId: $data->formId,
            userId: $data->userId,
            data: $data->data,
            status: FormSubmission::STATUS_NEW,
            meta: $data->meta,
            readAt: null,
            createdAt: $this->clock->now(),
        );

        $result = $this->submissionRepository->save($submission);
        $this->formRepository->incrementSubmissionsCount($data->formId);

        return $result;
    }
}