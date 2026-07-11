<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormController extends Controller
{
    public function show($id)
    {
        $form = Form::findOrFail($id);

        if (!$form->status) {
            return response()->json(['message' => 'Форма не активна'], 403);
        }

        return response()->json([
            'id' => $form->id,
            'title' => $form->title,
            'fields' => $form->fields ?? [],
        ]);
    }

    public function submit(Request $request, $id)
    {
        $form = Form::findOrFail($id);

        if (!$form->status) {
            return response()->json(['message' => 'Форма не активна'], 403);
        }

        // Собираем правила валидации из полей формы
        $rules = [];
        foreach ($form->fields as $field) {
            if ($field['required'] ?? false) {
                $rules[$field['name']] = 'required';
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        // Сохраняем ответ
        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'data' => $request->except('_token'),
            'meta' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
            ],
        ]);

        // Увеличиваем счетчик отправок
        $form->increment('submissions_count');

        return response()->json([
            'message' => 'Форма успешно отправлена',
            'submission_id' => $submission->id,
        ]);
    }
}
