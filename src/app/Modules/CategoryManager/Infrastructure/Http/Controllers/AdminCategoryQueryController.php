<?php

namespace App\Modules\CategoryManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CategoryManager\Application\DTO\CategoryFiltersData;
use App\Modules\CategoryManager\Application\UseCases\GetAllCategoriesUseCase;
use App\Modules\CategoryManager\Application\UseCases\GetCategoryByIdUseCase;
use App\Modules\CategoryManager\Application\UseCases\GetCategoryListUseCase;
use App\Modules\CategoryManager\Application\UseCases\GetCategoryTreeUseCase;
use App\Modules\CategoryManager\Infrastructure\Http\Requests\CategoryIndexRequest;
use App\Modules\CategoryManager\Infrastructure\Http\Resources\CategoryResource;
use App\Modules\CategoryManager\Infrastructure\Http\Resources\CategoryTreeResource;
use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminCategoryQueryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected GetCategoryListUseCase $getListUseCase,
        protected GetCategoryByIdUseCase $getByIdUseCase,
        protected GetAllCategoriesUseCase $getAllUseCase,
        protected GetCategoryTreeUseCase $getTreeUseCase,
    ) {
    }

    public function index(CategoryIndexRequest $request): Response
    {
        $this->authorize('viewAny', CategoryModel::class);

        $filters = CategoryFiltersData::fromArray($request->validated());
        $categories = $this->getListUseCase->execute($filters);
        $parentOptions = $this->getAllUseCase->execute();

        return Inertia::render('CategoryManager/Index', [
            'categories' => $categories,
            'parentOptions' => $parentOptions,
            'filters' => $request->validated(),
            'user' => auth()->user(),
            'title' => 'Категории',
        ]);
    }

    public function all(): JsonResponse
    {
        $this->authorize('viewAny', CategoryModel::class);

        $options = $this->getAllUseCase->execute();

        return response()->json($options);
    }

    public function tree(): JsonResponse
    {
        $this->authorize('viewAny', CategoryModel::class);

        $tree = $this->getTreeUseCase->execute();

        return response()->json(CategoryTreeResource::collection($tree));
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->getByIdUseCase->execute($id);

        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $this->authorize('view', $category);

        return response()->json(new CategoryResource($category));
    }
}
