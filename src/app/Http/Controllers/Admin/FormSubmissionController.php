<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\FormSubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FormSubmissionController extends Controller
{
    public function __construct(
        protected FormSubmissionService $submissionService
    ) {
    }

    /**
     * Список обращений
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'form_id']);
        $perPage = $request->get('per_page', 10);

        $submissions = $this->submissionService->getPaginated($filters, $perPage);
        $forms = Form::select('id', 'title', 'alias')->orderBy('title')->get();
        $unreadCount = $this->submissionService->countUnread();

        return Inertia::render('Admin/Submissions/Index', [
            'user' => auth()->user(),
            'submissions' => $submissions,
            'forms' => $forms,
            'filters' => $filters,
            'perPage' => (int) $perPage,
            'unreadCount' => $unreadCount,
            'title' => 'Обратная связь',
        ]);
    }

    /**
     * Просмотр обращения (отмечает как прочитанное)
     */
    public function show(int $id): JsonResponse
    {
        $submission = $this->submissionService->find($id);

        if (!$submission) {
            return response()->json([
                'message' => 'Обращение не найдено',
            ], 404);
        }

        // Отмечаем как прочитанное
        $this->submissionService->markAsRead($id);

        return response()->json([
            'submission' => $submission->load('form'),
        ]);
    }

    /**
     * Удаление обращения
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->submissionService->delete($id);

        if (!$deleted) {
            return response()->json([
                'message' => 'Обращение не найдено',
            ], 404);
        }

        return response()->json([
            'message' => 'Обращение удалено',
        ]);
    }

    /**
     * Отметить как прочитанное (для массовых операций)
     */
    public function markAsReadBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:form_submissions,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            if ($this->submissionService->markAsRead($id)) {
                $count++;
            }
        }

        return response()->json([
            'message' => "Отмечено как прочитанное: {$count}",
            'count' => $count,
        ]);
    }

    /**
     * Удаление массово
     */
    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:form_submissions,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            if ($this->submissionService->delete($id)) {
                $count++;
            }
        }

        return response()->json([
            'message' => "Удалено: {$count}",
            'count' => $count,
        ]);
    }
}
