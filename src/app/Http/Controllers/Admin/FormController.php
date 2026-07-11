<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Form\FormStoreRequest;
use App\Http\Requests\Admin\Form\FormUpdateRequest;
use App\Models\Form;
use App\Services\FormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class FormController extends Controller
{
    public function __construct(
        protected FormService $formService
    ) {
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status']);
        $perPage = $request->get('per_page', 10);

        $forms = $this->formService->getPaginated($filters, $perPage);

        return Inertia::render('Admin/Forms/Index', [
            'user' => auth()->user(),
            'forms' => $forms,
            'filters' => $filters,
            'perPage' => (int) $perPage,
            'title' => 'Формы',
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Forms/Create', [
            'user' => auth()->user(),
            'title' => 'Создать форму',
        ]);
    }

    public function store(FormStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['alias'])) {
            $data['alias'] = Str::slug($data['title']);
        }

        $data['fields'] = $data['fields'] ?? [];
        $data['settings'] = $data['settings'] ?? [
            'submit_text' => 'Отправить',
            'success_message' => 'Форма успешно отправлена!',
        ];
        $data['status'] = $data['status'] ?? true;
        $data['is_dynamic'] = $data['is_dynamic'] ?? false;
        $data['notification_emails'] = $data['notification_emails'] ?? [];

        $form = $this->formService->create($data);

        return response()->json([
            'message' => 'Форма создана',
            'form' => $form,
        ]);
    }

    public function edit(Form $form): Response
    {
        return Inertia::render('Admin/Forms/Edit', [
            'user' => auth()->user(),
            'form' => $form,
            'title' => 'Редактировать форму: ' . $form->title,
        ]);
    }

    public function update(FormUpdateRequest $request, Form $form): JsonResponse
    {
        $data = $request->validated();

        // Если алиас пустой или не передан - генерируем из названия
        if (empty($data['alias']) && !empty($data['title'])) {
            $data['alias'] = Str::slug($data['title']);
        }

        // Обрабатываем notification_emails (может быть пустым массивом)
        if (isset($data['notification_emails'])) {
            $data['notification_emails'] = is_array($data['notification_emails'])
                ? $data['notification_emails']
                : [];
        }

        // Убираем пустые значения, но сохраняем notification_emails если он пустой массив
        $filteredData = array_filter($data, function ($value, $key) {
            if ($key === 'notification_emails') {
                return true; // Всегда сохраняем, даже если пустой массив
            }
            return $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);

        $form->update($filteredData);

        return response()->json([
            'message' => 'Форма обновлена',
            'form' => $form->fresh(),
        ]);
    }

    public function updateStatus(Request $request, Form $form): JsonResponse
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $form->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Статус обновлен',
            'form' => $form,
        ]);
    }

    public function destroy(Form $form): JsonResponse
    {
        $this->formService->delete($form);

        return response()->json([
            'message' => 'Форма удалена',
        ]);
    }

    public function builder(Form $form): Response
    {
        return Inertia::render('Admin/Forms/Builder', [
            'user' => auth()->user(),
            'form' => $form,
            'title' => 'Конструктор формы: ' . $form->title,
        ]);
    }

    public function updateFields(Request $request, Form $form): JsonResponse
    {
        $request->validate([
            'fields' => 'required|array',
        ]);

        $form->update(['fields' => $request->fields]);

        return response()->json([
            'message' => 'Поля формы обновлены',
            'fields' => $form->fields,
        ]);
    }

    public function list(): JsonResponse
    {
        $forms = Form::select('id', 'title', 'alias', 'status', 'fields')
            ->orderBy('title')
            ->get();

        return response()->json($forms);
    }
}
