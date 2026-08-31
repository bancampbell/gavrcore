<?php

namespace App\Modules\FormBuilder\Infrastructure\Http\Controllers;

use App\Modules\FormBuilder\Application\DTO\SubmissionFiltersData;
use App\Modules\FormBuilder\Application\UseCases\GetSubmissionsUseCase;
use App\Modules\FormBuilder\Application\UseCases\GetUnreadSubmissionsCountUseCase;
use App\Modules\FormBuilder\Application\UseCases\ShowSubmissionUseCase;
use App\Modules\FormBuilder\Infrastructure\Http\Resources\SubmissionResource;
use App\Modules\FormBuilder\Infrastructure\Models\FormModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminSubmissionQueryController
{
    public function __construct(
        protected GetSubmissionsUseCase $getSubmissionsUseCase,
        protected ShowSubmissionUseCase $showSubmissionUseCase,
        protected GetUnreadSubmissionsCountUseCase $getUnreadSubmissionsCountUseCase,
    ) {}

    public function index(Request $request): Response
    {
        $filters = new SubmissionFiltersData(
            search: $request->get('search'),
            status: $request->get('status'),
            formId: $request->get('form_id') ? (int) $request->get('form_id') : null,
        );
        $perPage = (int) $request->get('per_page', 10);

        $submissions = $this->getSubmissionsUseCase->execute($filters, $perPage);

        return Inertia::render('FormBuilder/Submissions', [
            'user' => auth()->user(),
            'submissions' => $submissions,
            'forms' => FormModel::select('id', 'title', 'alias')->orderBy('title')->get(),
            'filters' => [
                'search' => $filters->search,
                'status' => $filters->status,
                'form_id' => $filters->formId,
            ],
            'perPage' => $perPage,
            'unreadCount' => $this->getUnreadSubmissionsCountUseCase->execute(),
            'title' => 'Обратная связь',
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $submission = $this->showSubmissionUseCase->execute($id);

        if (!$submission) {
            return response()->json(['message' => 'Обращение не найдено'], 404);
        }

        return response()->json([
            'submission' => new SubmissionResource($submission),
        ]);
    }
}