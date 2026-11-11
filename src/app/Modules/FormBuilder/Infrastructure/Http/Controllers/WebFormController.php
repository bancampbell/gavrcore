<?php

namespace App\Modules\FormBuilder\Infrastructure\Http\Controllers;

use App\Modules\CaptchaManager\Application\CaptchaManager;
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
    /**
     * Расширения, разрешённые к загрузке через публичные формы.
     * SVG намеренно исключён: это исполняемый XML, отдаваемый с same-origin
     * — stored-XSS под доменом сайта. Без этого whitelist любой аноним
     * загружал произвольные файлы на public-диск.
     */
    private const ALLOWED_UPLOAD_EXTENSIONS = 'jpg,jpeg,png,gif,webp,pdf,txt,doc,docx,xls,xlsx,zip';

    private const MAX_UPLOAD_KB = 10240;

    public function __construct(
        protected GetFormByIdUseCase $getFormByIdUseCase,
        protected CreateSubmissionUseCase $createSubmissionUseCase,
        protected SendSubmissionNotificationUseCase $sendSubmissionNotificationUseCase,
        protected CaptchaManager $captchaManager,
    ) {}

    public function show(int $id): JsonResponse
    {
        $form = $this->getFormByIdUseCase->execute($id);

        if (! $form) {
            return response()->json(['message' => 'Форма не найдена'], 404);
        }

        if (! $form->status->isPublished()) {
            return response()->json(['message' => 'Форма не активна'], 403);
        }

        $data = [
            'id' => $form->id,
            'title' => $form->title,
            'fields' => $form->fields->toArray(),
            'captcha_enabled' => $form->captchaEnabled,
        ];

        // Виджет отдаём только когда тумблер формы включён; фронт сам
        // не рендерит ничего для driver=none.
        if ($form->captchaEnabled) {
            $data['captcha'] = $this->captchaManager->getWidgetConfig();
        }

        return response()->json($data);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $form = $this->getFormByIdUseCase->execute($id);

        if (! $form) {
            return response()->json(['message' => 'Форма не найдена'], 404);
        }

        if (! $form->status->isPublished()) {
            return response()->json(['message' => 'Форма не активна'], 403);
        }

        // Капча обязательна только когда тумблер формы включён И выбран
        // рабочий драйвер (driver=none — токен не требуем даже при включённом
        // тумблере). Проверяем до валидации полей и сохранения вложений.
        if ($form->captchaEnabled && $this->captchaManager->driver()->getDriverName() !== 'none') {
            $token = $request->input('captcha_token');

            if (! is_string($token) || $token === '' || ! $this->captchaManager->verify($token, $request->ip())) {
                return response()->json([
                    'message' => 'Проверка капчи не пройдена. Обновите страницу и попробуйте отправить форму ещё раз.',
                    'errors' => ['captcha_token' => ['Проверка капчи не пройдена. Обновите страницу и попробуйте ещё раз.']],
                ], 422);
            }
        }

        $rules = [];
        foreach ($form->fields->toArray() as $field) {
            if ($field['required'] ?? false) {
                $rules[$field['name']] = 'required';
            }
        }

        // К вложениям применяем whitelist расширений и лимит размера —
        // независимо от того, описано ли поле в конфигурации формы.
        foreach ($request->allFiles() as $key => $file) {
            $fileRules = ['file', 'mimes:'.self::ALLOWED_UPLOAD_EXTENSIONS, 'max:'.self::MAX_UPLOAD_KB];
            $rules[$key] = isset($rules[$key])
                ? array_merge((array) $rules[$key], $fileRules)
                : $fileRules;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Токен капчи — служебное поле, в заявке не храним.
        $data = $request->except(['_token', 'captcha_token']);

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

        if (! $submission) {
            return response()->json(['message' => 'Ошибка сохранения'], 500);
        }

        $this->sendSubmissionNotificationUseCase->execute($form, $submission);

        return response()->json([
            'message' => 'Форма успешно отправлена',
            'submission_id' => $submission->id,
        ]);
    }
}
