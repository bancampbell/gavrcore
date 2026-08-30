<?php

namespace App\Modules\MenuManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MenuManager\Application\DTO\MenuTypeFiltersData;
use App\Modules\MenuManager\Application\UseCases\GetMenuTypeByIdUseCase;
use App\Modules\MenuManager\Application\UseCases\GetMenuTypeListUseCase;
use App\Modules\MenuManager\Infrastructure\Http\Resources\MenuTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;

class AdminMenuTypeQueryController extends Controller
{
    public function __construct(
        private GetMenuTypeListUseCase $getListUseCase,
        private GetMenuTypeByIdUseCase $getByIdUseCase,
    ) {}

    public function index(Request $request): Response|AnonymousResourceCollection
    {
        $filters = MenuTypeFiltersData::fromArray($request->only(['search', 'status']));
        $perPage = $request->get('per_page', 20);
        $menuTypes = $this->getListUseCase->execute($filters, $perPage);

        if ($request->wantsJson()) {
            return MenuTypeResource::collection($menuTypes);
        }

        return Inertia::render('Admin/Menu/Index', [
            'user' => auth()->user(),
            'menuTypes' => [
                'data' => $menuTypes->items(),
                'current_page' => $menuTypes->currentPage(),
                'last_page' => $menuTypes->lastPage(),
                'from' => $menuTypes->firstItem(),
                'to' => $menuTypes->lastItem(),
                'total' => $menuTypes->total(),
            ],
            'filters' => $request->only(['search', 'status']),
            'title' => 'Менеджер меню',
        ]);
    }

    public function show(int $id): MenuTypeResource|\Illuminate\Http\JsonResponse
    {
        $menuType = $this->getByIdUseCase->execute($id);
        if (! $menuType) return response()->json(['message' => 'Menu type not found'], 404);
        return new MenuTypeResource($menuType);
    }
}