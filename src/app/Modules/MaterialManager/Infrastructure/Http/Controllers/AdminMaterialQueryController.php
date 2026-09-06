<?php

namespace App\Modules\MaterialManager\Infrastructure\Http\Controllers;

use App\Modules\MaterialManager\Application\DTO\MaterialFiltersData;
use App\Modules\MaterialManager\Application\UseCases\GetMaterialForEditUseCase;
use App\Modules\MaterialManager\Application\UseCases\GetMaterialsUseCase;
use App\Modules\MaterialManager\Application\UseCases\GetTrashMaterialsUseCase;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Services\CategoryServiceInterface;
use App\Modules\MaterialManager\Infrastructure\Http\Requests\MaterialIndexRequest;
use App\Modules\MaterialManager\Infrastructure\Http\Resources\MaterialResource;
use App\Services\ThemeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

class AdminMaterialQueryController
{
    use AuthorizesRequests;

    public function __construct(
        private readonly GetMaterialsUseCase $getMaterials,
        private readonly GetTrashMaterialsUseCase $getTrashMaterials,
        private readonly GetMaterialForEditUseCase $getMaterialForEdit,
        private readonly CategoryServiceInterface $categoryService,
        private readonly ThemeService $themeService,
    ) {}

    public function index(MaterialIndexRequest $request): Response
    {
        $filters = MaterialFiltersData::fromRequest($request->validated());
        $materials = $this->getMaterials->execute($filters);

        $paginated = $materials->toArray();
        $paginated['data'] = array_map(
            fn (Material $m) => (new MaterialResource($m))->toArray(request()),
            $materials->items
        );

        return Inertia::render('MaterialManager/Index', [
            'materials' => $paginated,
            'categories' => $this->categoryService->getAll(),
            'authors' => \App\Modules\UserManager\Infrastructure\Models\UserModel::select('id', 'name')->orderBy('name')->get(),
            'filters' => $filters->toArray(),
            'perPage' => $filters->perPage,
            'user' => auth()->user(),
            'title' => 'Менеджер материалов',
            'isLanding' => $this->themeService->isLandingTheme(),
        ]);
    }

    public function trash(): Response
    {
        $materials = $this->getTrashMaterials->execute();

        $paginated = $materials->toArray();
        $paginated['data'] = array_map(
            fn (Material $m) => (new MaterialResource($m))->toArray(request()),
            $materials->items
        );

        return Inertia::render('MaterialManager/Trash', [
            'materials' => $paginated,
            'user' => auth()->user(),
            'title' => 'Корзина',
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Material::class);

        return Inertia::render('MaterialManager/Create', [
            'categories' => $this->categoryService->getAll(),
            'user' => auth()->user(),
            'title' => 'Создать материал',
        ]);
    }

    public function edit(int $id): Response
    {
        $material = $this->getMaterialForEdit->execute($id);
        $this->authorize('update', $material);

        return Inertia::render('MaterialManager/Edit', [
            'material' => $material->toArray(),
            'categories' => $this->categoryService->getAll(),
            'user' => auth()->user(),
            'title' => 'Редактировать материал',
        ]);
    }

    public function list(): \Illuminate\Http\JsonResponse
    {
        $result = $this->getMaterials->execute(
            MaterialFiltersData::fromRequest(['per_page' => 1000])
        );

        return response()->json(
            array_map(
                fn (Material $m) => (new MaterialResource($m))->toArray(request()),
                $result->items
            )
        );
    }
}
