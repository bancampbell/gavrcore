<?php

namespace App\Modules\MenuManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MenuManager\Application\DTO\MenuItemFiltersData;
use App\Modules\MenuManager\Application\UseCases\GetAllMenuItemsUseCase;
use App\Modules\MenuManager\Application\UseCases\GetMenuItemByIdUseCase;
use App\Modules\MenuManager\Application\UseCases\GetMenuItemListUseCase;
use App\Modules\MenuManager\Application\UseCases\GetMenuItemTreeUseCase;
use App\Modules\MenuManager\Infrastructure\Http\Resources\MenuItemResource;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;

class AdminMenuItemQueryController extends Controller
{
    public function __construct(
        private GetMenuItemListUseCase $getListUseCase,
        private GetMenuItemByIdUseCase $getByIdUseCase,
        private GetMenuItemTreeUseCase $getTreeUseCase,
        private GetAllMenuItemsUseCase $getAllUseCase,
    ) {}

    public function index(Request $request, int $menuTypeId): Response|AnonymousResourceCollection
    {
        $filters = MenuItemFiltersData::fromArray($request->only(['search', 'status']));
        $perPage = $request->get('per_page', 20);
        $menuItems = $this->getListUseCase->execute($menuTypeId, $filters, $perPage);

        if ($request->wantsJson()) {
            return MenuItemResource::collection($menuItems);
        }

        $menuType = MenuTypeModel::findOrFail($menuTypeId);
        return Inertia::render('Admin/Menu/MenuItems', [
            'user' => auth()->user(),
            'menuTypeId' => $menuTypeId,
            'menuTypeTitle' => $menuType->title,
            'menuItems' => [
                'data' => $menuItems->items(),
                'current_page' => $menuItems->currentPage(),
                'last_page' => $menuItems->lastPage(),
                'from' => $menuItems->firstItem(),
                'to' => $menuItems->lastItem(),
                'total' => $menuItems->total(),
            ],
            'filters' => $request->only(['search', 'status']),
            'title' => "Пункты меню: {$menuType->title}",
        ]);
    }

    public function tree(int $menuTypeId): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->getTreeUseCase->execute($menuTypeId));
    }

    public function show(int $id): MenuItemResource|\Illuminate\Http\JsonResponse
    {
        $menuItem = $this->getByIdUseCase->execute($id);
        if (! $menuItem) return response()->json(['message' => 'Menu item not found'], 404);
        return new MenuItemResource($menuItem);
    }

    public function getAllItemsPage(Request $request): Response
    {
        $filters = MenuItemFiltersData::fromArray($request->only(['search', 'status']));
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);
        $menuItems = $this->getAllUseCase->execute($filters, $perPage, $page);

        return Inertia::render('Admin/Menu/MenuItems', [
            'user' => auth()->user(),
            'menuTypeId' => null,
            'menuTypeTitle' => 'Все пункты',
            'menuItems' => [
                'data' => $menuItems->items(),
                'current_page' => $menuItems->currentPage(),
                'last_page' => $menuItems->lastPage(),
                'from' => $menuItems->firstItem(),
                'to' => $menuItems->lastItem(),
                'total' => $menuItems->total(),
            ],
            'filters' => $request->only(['search', 'status']),
            'title' => 'Все пункты меню',
        ]);
    }

    public function getAllItems(Request $request): \Illuminate\Http\JsonResponse
    {
        $filters = MenuItemFiltersData::fromArray($request->only(['search', 'status']));
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);
        $menuItems = $this->getAllUseCase->execute($filters, $perPage, $page);

        return response()->json([
            'data' => $menuItems->items(),
            'current_page' => $menuItems->currentPage(),
            'last_page' => $menuItems->lastPage(),
            'from' => $menuItems->firstItem(),
            'to' => $menuItems->lastItem(),
            'total' => $menuItems->total(),
        ]);
    }
}