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

        $submittedData = $request->all();

        $rules = [];
        $attributes = [];
        $messages = [
            'required' => 'Поле «:attribute» обязательно для заполнения.',
            'email' => 'Поле «:attribute» должно содержать корректный email-адрес.',
            'file' => 'Поле «:attribute» должно содержать файл.',
            'mimes' => 'Файл «:attribute» имеет недопустимый формат. Разрешённые форматы: :values.',
            'max' => 'Файл «:attribute» слишком большой: максимум :max КБ.',
            'numeric' => 'Поле «:attribute» должно быть числом.',
            'min' => 'Поле «:attribute» не должно быть меньше :min.',
        ];

        foreach ($form->fields->toArray() as $field) {
            // В текстах ошибок поле называется по метке из конфигурации;
            // метки нет — fallback на техническое имя.
            $attributes[$field['name']] = ! empty($field['label']) ? $field['label'] : $field['name'];

            $fieldRules = [];

            // Скрытое условиями поле не валидируем как required: на клиенте
            // оно скрыто и не отправляется — иначе форма с условиями падает
            // с 422 на полях, которые пользователь физически не заполнял.
            // Логика видимости зеркалит checkFieldVisibility на фронте.
            if (($field['required'] ?? false) && $this->isFieldVisible($field, $submittedData)) {
                $fieldRules[] = 'required';
            }

            // Присланное значение должно соответствовать типу поля.
            switch ($field['type'] ?? null) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    if (isset($field['min_value']) && $field['min_value'] !== '' && $field['min_value'] !== null) {
                        $fieldRules[] = 'min:'.$field['min_value'];
                    }
                    if (isset($field['max_value']) && $field['max_value'] !== '' && $field['max_value'] !== null) {
                        $fieldRules[] = 'max:'.$field['max_value'];
                        // Для числового поля max — значение, а не размер файла.
                        $messages['max.'.$field['name']] = 'Поле «:attribute» не должно быть больше :max.';
                    }
                    break;
            }

            if (count($fieldRules) > 0) {
                $rules[$field['name']] = $fieldRules;
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

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

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

    /**
     * Видимость поля по его условиям (зеркало checkFieldVisibility во
     * фронтовом FormRenderer): поле без условий всегда видимо, иначе
     * результат по условиям объединяется по conditions_logic (and по умолчанию).
     */
    private function isFieldVisible(array $field, array $data): bool
    {
        $conditions = $field['conditions'] ?? [];

        if (count($conditions) === 0) {
            return true;
        }

        $results = [];

        foreach ($conditions as $condition) {
            if (empty($condition['field'])) {
                $results[] = true;
                continue;
            }

            $value = $data[$condition['field']] ?? '';
            $expected = $condition['value'] ?? '';

            $results[] = match ($condition['operator'] ?? null) {
                'equals' => (string) $value === (string) $expected,
                'not_equals' => (string) $value !== (string) $expected,
                default => true,
            };
        }

        if (($field['conditions_logic'] ?? 'and') === 'or') {
            return in_array(true, $results, true);
        }

        return ! in_array(false, $results, true);
    }
}
