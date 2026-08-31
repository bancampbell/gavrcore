<?php

namespace App\Modules\FormBuilder\Infrastructure\Http\Controllers;

use App\Modules\FormBuilder\Application\DTO\CreateSubmissionData;
use App\Modules\FormBuilder\Application\UseCases\CreateSubmissionUseCase;
use App\Modules\FormBuilder\Application\UseCases\GetFormByIdUseCase;
use App\Modules\FormBuilder\Application\UseCases\SendSubmissionNotificationUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WebFormController
{
    public function __construct(
        protected GetFormByIdUseCase $getFormByIdUseCase,
        protected CreateSubmissionUseCase $createSubmissionUseCase,
        protected SendSubmissionNotificationUseCase $sendSubmissionNotificationUseCase,
    ) {}

    public function show(int $id): JsonResponse
    {
        $form = $this->getFormByIdUseCase->execute($id);

        if (!$form) {
            return response()->json(['message' => 'Форма не найдена'], 404);
        }

        if (!$form->status->isPublished()) {
            return response()->json(['message' => 'Форма не активна'], 403);
        }

        return response()->json([
            'id' => $form->id,
            'title' => $form->title,
            'fields' => $form->fields->toArray(),
        ]);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $form = $this->getFormByIdUseCase->execute($id);

        if (!$form) {
            return response()->json(['message' => 'Форма не найдена'], 404);
        }

        if (!$form->status->isPublished()) {
            return response()->json(['message' => 'Форма не активна'], 403);
        }

        $rules = [];
        foreach ($form->fields->toArray() as $field) {
            if ($field['required'] ?? false) {
                $rules[$field['name']] = 'required';
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->except('_token');

        foreach ($request->allFiles() as $key => $file) {
            if (is_array($file)) {
                $paths = [];
                foreach ($file as $singleFile) {
                    if ($singleFile instanceof UploadedFile) {
                        $paths[] = $singleFile->store('submissions', 'public');
                    }
                }
                $data[$key] = $paths;
            } elseif ($file instanceof UploadedFile) {
                $data[$key] = $file->store('submissions', 'public');
            }
        }

        $userId = Auth::check() ? Auth::id() : null;

        $submission = $this->createSubmissionUseCase->execute(new CreateSubmissionData(
            formId: $form->id,
            userId: $userId,
            data: $data,
            meta: [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
            ],
        ));

        if (!$submission) {
            return response()->json(['message' => 'Ошибка сохранения'], 500);
        }

        $this->sendSubmissionNotificationUseCase->execute($form, $submission);

        return response()->json([
            'message' => 'Форма успешно отправлена',
            'submission_id' => $submission->id,
        ]);
    }
}