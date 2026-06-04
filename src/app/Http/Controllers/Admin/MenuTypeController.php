<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Menu\MenuTypeRequest;
use App\Http\Resources\Admin\Menu\MenuTypeResource;
use App\Services\MenuTypeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuTypeController extends Controller
{
    protected MenuTypeService $service;

    public function __construct(MenuTypeService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
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
        ]);
    }

    public function store(MenuTypeRequest $request)
    {
        $menuType = $this->service->create($request->validated());
        return new MenuTypeResource($menuType);
    }

    public function show(int $id)
    {
        $menuType = $this->service->findById($id);

        if (!$menuType) {
            return response()->json(['message' => 'Menu type not found'], 404);
        }

        return new MenuTypeResource($menuType);
    }

    public function update(MenuTypeRequest $request, int $id)
    {
        $menuType = $this->service->update($id, $request->validated());
        return new MenuTypeResource($menuType);
    }

    public function destroy(int $id)
    {
        $deleted = $this->service->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Menu type not found'], 404);
        }

        return response()->json(['message' => 'Deleted successfully'], 200);
    }

    public function updateOrdering(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:menu_types,id',
            'order.*.ordering' => 'required|integer',
        ]);

        $this->service->updateOrdering($request->order);
        return response()->json(['message' => 'Ordering updated successfully']);
    }
}
