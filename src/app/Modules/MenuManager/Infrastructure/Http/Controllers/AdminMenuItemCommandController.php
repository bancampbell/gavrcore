<?php

namespace App\Modules\MenuManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MenuManager\Application\DTO\CreateMenuItemData;
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
use Illuminate\Http\JsonResponse;

class AdminMenuItemCommandController extends Controller
{
    public function __construct(
        private CreateMenuItemUseCase $createUseCase,
        private UpdateMenuItemUseCase $updateUseCase,
        private DeleteMenuItemUseCase $deleteUseCase,
        private UpdateMenuItemOrderingUseCase $updateOrderingUseCase,
        private UpdateMenuItemStatusUseCase $updateStatusUseCase,
    ) {}

    public function store(CreateMenuItemRequest $request, int $menuTypeId): MenuItemResource
    {
        $data = new CreateMenuItemData(
            menuTypeId: $menuTypeId,
            parentId: $request->input('parent_id'),
            title: $request->input('title'),
            alias: $request->input('alias'),
            linkType: $request->input('link_type', 'url'),
            linkValue: $request->input('link_value'),
            target: $request->input('target', '_self'),
            status: $request->boolean('status', true),
            access: $request->input('access', 'all'),
            language: $request->input('language', 'all'),
            position: $request->input('position'),
            afterId: $request->input('after_id'),
        );
        $menuItem = $this->createUseCase->execute($data);
        return new MenuItemResource($menuItem);
    }

    public function update(UpdateMenuItemRequest $request, int $id): MenuItemResource
    {
        $data = new UpdateMenuItemData(
            parentId: $request->input('parent_id'),
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
        );
        $menuItem = $this->updateUseCase->execute($id, $data);
        return new MenuItemResource($menuItem);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->deleteUseCase->execute($id);
        if (! $deleted) return response()->json(['message' => 'Menu item not found'], 404);
        return response()->json(['message' => 'Deleted successfully'], 200);
    }

    public function updateStatus(int $id, UpdateStatusRequest $request): JsonResponse
    {
        $this->updateStatusUseCase->execute($id, $request->boolean('status'));
        return response()->json(['message' => 'Status updated successfully']);
    }

    public function updateOrdering(UpdateOrderingRequest $request): JsonResponse
    {
        $this->updateOrderingUseCase->execute($request->input('order'));
        return response()->json(['message' => 'Ordering updated successfully']);
    }
}