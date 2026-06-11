<?php

namespace App\Http\Controllers\Admin;

use App\DTO\CategoryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\CategoryIndexRequest;
use App\Http\Requests\Admin\Category\CategoryStoreRequest;
use App\Http\Requests\Admin\Category\CategoryUpdateRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected CategoryService $categoryService
    ) {
    }

    public function index(CategoryIndexRequest $request): Response
    {
        $this->authorize('viewAny', Category::class);

        $filters = $request->validated();
        $categories = $this->categoryService->getPaginated($filters);
        $parentOptions = $this->categoryService->getAllForSelect();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'parentOptions' => $parentOptions,
            'filters' => $filters,
            'user' => auth()->user(),
        ]);
    }

    public function store(CategoryStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $this->categoryService->create(CategoryData::fromArray($request->validated()));

        return redirect()->route('admin.categories.index')
            ->with('success', 'Категория создана');
    }

    public function update(CategoryUpdateRequest $request, int $id): RedirectResponse
    {
        $category = $this->categoryService->find($id);
        $this->authorize('update', $category);

        $this->categoryService->update($category, CategoryData::fromArray($request->validated()));

        return redirect()->route('admin.categories.index')
            ->with('success', 'Категория обновлена');
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        $this->categoryService->delete($category);

        return response()->json(['success' => true, 'message' => 'Категория удалена']);
    }
}
