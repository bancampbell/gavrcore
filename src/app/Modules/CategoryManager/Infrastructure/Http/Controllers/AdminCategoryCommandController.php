<?php

namespace App\Modules\CategoryManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CategoryManager\Application\DTO\CategoryData;
use App\Modules\CategoryManager\Application\UseCases\BulkDeleteCategoriesUseCase;
use App\Modules\CategoryManager\Application\UseCases\CreateCategoryUseCase;
use App\Modules\CategoryManager\Application\UseCases\DeleteCategoryUseCase;
use App\Modules\CategoryManager\Application\UseCases\UpdateCategoryUseCase;
use App\Modules\CategoryManager\Infrastructure\Http\Requests\CreateCategoryRequest;
use App\Modules\CategoryManager\Infrastructure\Http\Requests\UpdateCategoryRequest;
use App\Modules\CategoryManager\Infrastructure\Http\Resources\CategoryResource;
use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCategoryCommandController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected CreateCategoryUseCase $createUseCase,
        protected UpdateCategoryUseCase $updateUseCase,
        protected DeleteCategoryUseCase $deleteUseCase,
        protected BulkDeleteCategoriesUseCase $bulkDeleteUseCase,
    ) {
    }

    public function store(CreateCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', CategoryModel::class);

        $category = $this->createUseCase->execute(CategoryData::fromArray($request->validated()));

        return response()->json([
            'success' => true,
            'message' => 'Категория создана',
            'data' => new CategoryResource($category),
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = CategoryModel::findOrFail($id);
        $this->authorize('update', $category);

        $category = $this->updateUseCase->execute($category, CategoryData::fromArray($request->validated()));

        return response()->json([
            'success' => true,
            'message' => 'Категория обновлена',
            'data' => new CategoryResource($category),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $category = CategoryModel::findOrFail($id);
            $this->authorize('delete', $category);

            $this->deleteUseCase->execute($category);

            return response()->json(['success' => true, 'message' => 'Категория удалена']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:categories,id',
        ]);

        $categories = CategoryModel::whereIn('id', $validated['ids'])->get();

        foreach ($categories as $category) {
            $this->authorize('delete', $category);
        }

        try {
            $this->bulkDeleteUseCase->execute($validated['ids']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Категории удалены']);
    }
}
