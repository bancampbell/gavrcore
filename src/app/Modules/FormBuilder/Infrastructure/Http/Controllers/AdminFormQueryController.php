<?php

namespace App\Modules\FormBuilder\Infrastructure\Http\Controllers;

use App\Modules\FormBuilder\Application\DTO\FormFiltersData;
use App\Modules\FormBuilder\Application\UseCases\GetFormByIdUseCase;
use App\Modules\FormBuilder\Application\UseCases\GetFormListUseCase;
use App\Modules\FormBuilder\Application\UseCases\GetFormsUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminFormQueryController
{
    public function __construct(
        protected GetFormsUseCase $getFormsUseCase,
        protected GetFormByIdUseCase $getFormByIdUseCase,
        protected GetFormListUseCase $getFormListUseCase,
    ) {}

    public function index(Request $request): Response
    {
        $filters = new FormFiltersData(
            search: $request->get('search'),
            status: $request->has('status') ? (bool) $request->get('status') : null,
        );
        $perPage = (int) $request->get('per_page', 10);

        $forms = $this->getFormsUseCase->execute($filters, $perPage);

        return Inertia::render('FormBuilder/FormIndex', [
            'user' => auth()->user(),
            'forms' => $forms,
            'filters' => [
                'search' => $filters->search,
                'status' => $request->get('status'),
            ],
            'perPage' => $perPage,
            'title' => 'Формы',
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('FormBuilder/Create', [
            'user' => auth()->user(),
            'title' => 'Создать форму',
        ]);
    }

    public function edit(int $id): Response
    {
        $form = $this->getFormByIdUseCase->execute($id);
        if (!$form) abort(404);

        return Inertia::render('FormBuilder/Edit', [
            'user' => auth()->user(),
            'form' => [
                'id' => $form->id,
                'title' => $form->title,
                'alias' => $form->alias,
                'description' => $form->description,
                'status' => $form->status->value(),
                'fields' => $form->fields->toArray(),
                'settings' => $form->settings->toArray(),
                'notification_emails' => $form->notificationEmails,
                'is_dynamic' => $form->isDynamic,
                'submissions_count' => $form->submissionsCount,
                'created_at' => $form->createdAt?->format('Y-m-d H:i:s'),
                'updated_at' => $form->updatedAt?->format('Y-m-d H:i:s'),
            ],
            'title' => 'Редактировать форму: ' . $form->title,
        ]);
    }

    public function builder(int $id): Response
    {
        $form = $this->getFormByIdUseCase->execute($id);
        if (!$form) abort(404);

        return Inertia::render('FormBuilder/Builder', [
            'user' => auth()->user(),
            'form' => [
                'id' => $form->id,
                'title' => $form->title,
                'alias' => $form->alias,
                'description' => $form->description,
                'status' => $form->status->value(),
                'fields' => $form->fields->toArray(),
                'settings' => $form->settings->toArray(),
                'notification_emails' => $form->notificationEmails,
                'is_dynamic' => $form->isDynamic,
                'submissions_count' => $form->submissionsCount,
                'created_at' => $form->createdAt?->format('Y-m-d H:i:s'),
                'updated_at' => $form->updatedAt?->format('Y-m-d H:i:s'),
            ],
            'title' => 'Конструктор формы: ' . $form->title,
        ]);
    }

    public function list()
    {
        return response()->json($this->getFormListUseCase->execute());
    }
}