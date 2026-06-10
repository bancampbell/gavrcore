<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Menu\MenuItemRequest;
use App\Http\Resources\Admin\Menu\MenuItemResource;
use App\Models\MenuItem;
use App\Services\MenuItemService;
use App\Models\MenuType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Response;
use Inertia\Inertia;

class MenuItemController extends Controller
{
    protected MenuItemService $service;

    public function __construct(MenuItemService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request, int $menuTypeId): Response|AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'status']);
        $perPage = $request->get('per_page', 20);

        $menuItems = $this->service->getAll($menuTypeId, $filters, $perPage);

        if ($request->wantsJson()) {
            return MenuItemResource::collection($menuItems);
        }

        $menuType = MenuType::findOrFail($menuTypeId);

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
            'filters' => $filters,
        ]);
    }

    public function tree(int $menuTypeId): JsonResponse
    {
        $tree = $this->service->getTree($menuTypeId);
        return response()->json($tree);
    }

    public function store(MenuItemRequest $request, int $menuTypeId): MenuItemResource
    {
        $data = $request->validated();
        $data['menu_type_id'] = $menuTypeId;

        $menuItem = $this->service->create($menuTypeId, $data);

        return new MenuItemResource($menuItem);
    }

    public function show(int $id): MenuItemResource|JsonResponse
    {
        $menuItem = $this->service->findById($id);

        if (!$menuItem) {
            return response()->json(['message' => 'Menu item not found'], 404);
        }

        return new MenuItemResource($menuItem);
    }

    public function update(MenuItemRequest $request, int $id): MenuItemResource
    {
        $menuItem = $this->service->update($id, $request->validated());
        return new MenuItemResource($menuItem);
    }

    public function getAllItems(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status']);
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);

        $query = MenuItem::with('menuType')->orderBy('created_at', 'desc');

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $menuItems = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $menuItems->items(),
            'current_page' => $menuItems->currentPage(),
            'last_page' => $menuItems->lastPage(),
            'from' => $menuItems->firstItem(),
            'to' => $menuItems->lastItem(),
            'total' => $menuItems->total(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->service->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Menu item not found'], 404);
        }

        return response()->json(['message' => 'Deleted successfully'], 200);
    }

    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $this->service->updateStatus($id, $request->status);
        return response()->json(['message' => 'Status updated successfully']);
    }

    public function updateOrdering(Request $request): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:menu_items,id',
            'order.*.ordering' => 'required|integer',
        ]);

        $this->service->updateOrdering($request->order);
        return response()->json(['message' => 'Ordering updated successfully']);
    }
}
