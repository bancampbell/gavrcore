<?php

namespace App\Modules\MenuManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MenuManager\Application\DTO\CreateMenuItemData;
use App\Modules\MenuManager\Application\DTO\Optional;
use App\Modules\MenuManager\Application\DTO\UpdateMenuItemData;
use App\Modules\MenuManager\Application\UseCases\CreateMenuItemUseCase;
use App\Modules\MenuManager\Application\UseCases\DeleteMenuItemUseCase;
use App\Modules\MenuManager\Application\UseCases\UpdateMenuItemOrderingUseCase;
use App\Modules\MenuManager\Application\UseCases\UpdateMenuItemStatusUseCase;
use App\Modules\MenuManager\Application\UseCases\UpdateMenuItemUseCase;
use App\Modules\MenuManager\Infrastructure\Http\Requests\CreateMenuItemRequest;
use App\Modules\MenuManager\Infrastructure\Http\Requests\UpdateMenuItemRequest;
use App\Modules\MenuManager\Infrastructure\Http\Requests\UpdateOrderingRequest;
use App\Modules\MenuManager\Infrastructure\Http\Requests\UpdateStatusRequest;
use App\Modules\MenuManager\Infrastructure\Http\Resources\MenuItemResource;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class AdminMenuItemCommandController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private CreateMenuItemUseCase $createUseCase,
        private UpdateMenuItemUseCase $updateUseCase,
        private DeleteMenuItemUseCase $deleteUseCase,
        private UpdateMenuItemOrderingUseCase $updateOrderingUseCase,
        private UpdateMenuItemStatusUseCase $updateStatusUseCase,
    ) {}

    public function store(CreateMenuItemRequest $request, int $menuTypeId): JsonResponse
    {
        $this->authorize('create', MenuItemModel::class);

        $data = new CreateMenuItemData(
            menuTypeId: $menuTypeId,
            parentId: $request->input('parent_id'),
            title: $request->input('title'),
            alias: $request->input('alias'),
            linkType: $request->input('link_type') ?? 'url',
            linkValue: $request->input('link_value'),
            target: $request->input('target') ?? '_self',
            status: $request->boolean('status', true),
            access: $request->input('access') ?? 'all',
            language: $request->input('language') ?? 'all',
            position: $request->input('position'),
            afterId: $request->input('after_id'),
        );
        $menuItem = $this->createUseCase->execute($data);
        return response()->json(['data' => new MenuItemResource($menuItem)]);
    }

    public function update(UpdateMenuItemRequest $request, int $id): JsonResponse
    {
        $menuItem = MenuItemModel::find($id);
        if (! $menuItem) abort(404);
        $this->authorize('update', $menuItem);

        $data = new UpdateMenuItemData(
            parentId: $request->has('parent_id') ? $request->input('parent_id') : Optional::value(),
            title: $request->input('title'),
            alias: $request->input('alias'),
            linkType: $request->input('link_type'),
            linkValue: $request->input('link_value'),
            target: $request->input('target'),
            status: $request->has('status') ? $request->boolean('status') : null,
            access: $request->input('access'),
            language: $request->input('language'),
            position: $request->input('position'),
            afterId: $request->input('after_id'),
            menuTypeId: $request->input('menu_type_id'),
        );
        $menuItem = $this->updateUseCase->execute($id, $data);
        return response()->json(['data' => new MenuItemResource($menuItem)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $menuItem = MenuItemModel::find($id);
        if (! $menuItem) abort(404);
        $this->authorize('delete', $menuItem);

        $deleted = $this->deleteUseCase->execute($id);
        if (! $deleted) return response()->json(['message' => 'Menu item not found'], 404);
        return response()->json(['message' => 'Deleted successfully'], 200);
    }

    public function updateStatus(int $id, UpdateStatusRequest $request): JsonResponse
    {
        $menuItem = MenuItemModel::find($id);
        if (! $menuItem) abort(404);
        $this->authorize('update', $menuItem);

        $this->updateStatusUseCase->execute($id, $request->boolean('status'));
        return response()->json(['message' => 'Status updated successfully']);
    }

    public function updateOrdering(UpdateOrderingRequest $request): JsonResponse
    {
        $this->authorize('update', MenuItemModel::class);

        $this->updateOrderingUseCase->execute($request->input('order'));
        return response()->json(['message' => 'Ordering updated successfully']);
    }
}
