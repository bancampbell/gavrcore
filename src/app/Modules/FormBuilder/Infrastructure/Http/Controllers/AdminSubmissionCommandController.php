<?php

namespace App\Modules\FormBuilder\Infrastructure\Http\Controllers;

use App\Modules\FormBuilder\Application\UseCases\DeleteSubmissionUseCase;
use App\Modules\FormBuilder\Application\UseCases\MarkSubmissionsAsReadUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSubmissionCommandController
{
    public function __construct(
        protected DeleteSubmissionUseCase $deleteSubmissionUseCase,
        protected MarkSubmissionsAsReadUseCase $markSubmissionsAsReadUseCase,
    ) {}

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->deleteSubmissionUseCase->execute($id);
        if (!$deleted) {
            return response()->json(['message' => 'Обращение не найдено'], 404);
        }
        return response()->json(['message' => 'Обращение удалено']);
    }

    public function markAsReadBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:form_submissions,id',
        ]);

        $count = $this->markSubmissionsAsReadUseCase->execute($request->input('ids'));

        return response()->json([
            'message' => "Отмечено как прочитанное: {$count}",
            'count' => $count,
        ]);
    }

    public function destroyBulk(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:form_submissions,id',
        ]);

        $count = 0;
        foreach ($request->input('ids') as $id) {
            if ($this->deleteSubmissionUseCase->execute($id)) $count++;
        }

        return response()->json([
            'message' => "Удалено: {$count}",
            'count' => $count,
        ]);
    }
}