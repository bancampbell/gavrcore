<?php

namespace App\Modules\FormBuilder\Infrastructure\Http\Controllers;

use App\Modules\FormBuilder\Application\DTO\CreateFormData;
use App\Modules\FormBuilder\Application\DTO\UpdateFormData;
use App\Modules\FormBuilder\Application\DTO\UpdateFormFieldsData;
use App\Modules\FormBuilder\Application\UseCases\CreateFormUseCase;
use App\Modules\FormBuilder\Application\UseCases\DeleteFormUseCase;
use App\Modules\FormBuilder\Application\UseCases\GetFormByIdUseCase;
use App\Modules\FormBuilder\Application\UseCases\UpdateFormFieldsUseCase;
use App\Modules\FormBuilder\Application\UseCases\UpdateFormStatusUseCase;
use App\Modules\FormBuilder\Application\UseCases\UpdateFormUseCase;
use App\Modules\FormBuilder\Infrastructure\Http\Requests\CreateFormRequest;
use App\Modules\FormBuilder\Infrastructure\Http\Requests\UpdateFormFieldsRequest;
use App\Modules\FormBuilder\Infrastructure\Http\Requests\UpdateFormRequest;
use App\Modules\FormBuilder\Infrastructure\Http\Requests\UpdateFormStatusRequest;
use Illuminate\Http\JsonResponse;

class AdminFormCommandController
{
    public function __construct(
        protected CreateFormUseCase $createFormUseCase,
        protected UpdateFormUseCase $updateFormUseCase,
        protected DeleteFormUseCase $deleteFormUseCase,
        protected UpdateFormStatusUseCase $updateFormStatusUseCase,
        protected UpdateFormFieldsUseCase $updateFormFieldsUseCase,
        protected GetFormByIdUseCase $getFormByIdUseCase,
    ) {}

    public function store(CreateFormRequest $request): JsonResponse
    {
        $data = new CreateFormData(
            title: $request->validated('title'),
            alias: $request->validated('alias'),
            description: $request->validated('description'),
            fields: $request->validated('fields', []),
            settings: $request->validated('settings', [
                'submit_text' => 'Отправить',
                'success_message' => 'Форма успешно отправлена!',
            ]),
            notificationEmails: $request->validated('notification_emails', []),
            status: $request->validated('status', true),
            isDynamic: $request->validated('is_dynamic', false),
        );

        $form = $this->createFormUseCase->execute($data);

        return response()->json([
            'message' => 'Форма создана',
            'form' => $form,
        ]);
    }

    public function update(UpdateFormRequest $request, int $id): JsonResponse
    {
        $data = new UpdateFormData(
            title: $request->validated('title'),
            alias: $request->validated('alias'),
            description: $request->validated('description'),
            fields: $request->validated('fields'),
            settings: $request->validated('settings'),
            notificationEmails: $request->validated('notification_emails', []),
            status: $request->validated('status', true),
            isDynamic: $request->validated('is_dynamic'),
        );

        $form = $this->updateFormUseCase->execute($id, $data);

        if (!$form) {
            return response()->json(['message' => 'Форма не найдена'], 404);
        }

        return response()->json([
            'message' => 'Форма обновлена',
            'form' => $form,
        ]);
    }

    public function updateStatus(UpdateFormStatusRequest $request, int $id): JsonResponse
    {
        $success = $this->updateFormStatusUseCase->execute($id, $request->validated('status'));

        if (!$success) {
            return response()->json(['message' => 'Форма не найдена'], 404);
        }

        $form = $this->getFormByIdUseCase->execute($id);

        return response()->json([
            'message' => 'Статус обновлен',
            'form' => $form,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->deleteFormUseCase->execute($id);

        if (!$deleted) {
            return response()->json(['message' => 'Форма не найдена'], 404);
        }

        return response()->json(['message' => 'Форма удалена']);
    }

    public function updateFields(UpdateFormFieldsRequest $request, int $id): JsonResponse
    {
        $data = new UpdateFormFieldsData(
            formId: $id,
            fields: $request->validated('fields'),
        );

        $form = $this->updateFormFieldsUseCase->execute($data);

        if (!$form) {
            return response()->json(['message' => 'Форма не найдена'], 404);
        }

        return response()->json([
            'message' => 'Поля формы обновлены',
            'fields' => $form->fields->toArray(),
        ]);
    }
}