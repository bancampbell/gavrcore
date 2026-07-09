<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Menu\MenuTypeRequest;
use App\Http\Resources\Admin\Menu\MenuTypeResource;
use App\Services\MenuTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;

class MenuTypeController extends Controller
{
    protected MenuTypeService $service;

    public function __construct(MenuTypeService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): Response|AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'status']);
        $perPage = $request->get('per_page', 20);

        $menuTypes = $this->service->getAll($filters, $perPage);

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
            'filters' => $filters,
            'title' => 'Менеджер меню',
        ]);
    }

    public function store(MenuTypeRequest $request): MenuTypeResource
    {
        $menuType = $this->service->create($request->validated());

        return new MenuTypeResource($menuType);
    }

    public function show(int $id): MenuTypeResource|JsonResponse
    {
        $menuType = $this->service->findById($id);

        if (! $menuType) {
            return response()->json(['message' => 'Menu type not found'], 404);
        }

        return new MenuTypeResource($menuType);
    }

    public function update(MenuTypeRequest $request, int $id): MenuTypeResource
    {
        $menuType = $this->service->update($id, $request->validated());

        return new MenuTypeResource($menuType);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->service->delete($id);

        if (! $deleted) {
            return response()->json(['message' => 'Menu type not found'], 404);
        }

        return response()->json(['message' => 'Deleted successfully'], 200);
    }

    public function updateOrdering(Request $request): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:menu_types,id',
            'order.*.ordering' => 'required|integer',
        ]);

        $this->service->updateOrdering($request->order);

        return response()->json(['message' => 'Ordering updated successfully']);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $updated = $this->service->updateStatus($id, $request->status);

        if (! $updated) {
            return response()->json(['message' => 'Menu type not found'], 404);
        }

        return response()->json([
            'message' => 'Статус обновлен',
            'status' => $request->status
        ]);
    }
}
