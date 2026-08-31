<?php

namespace App\Modules\MenuManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MenuManager\Application\DTO\CreateMenuTypeData;
use App\Modules\MenuManager\Application\DTO\UpdateMenuTypeData;
use App\Modules\MenuManager\Application\UseCases\CreateMenuTypeUseCase;
use App\Modules\MenuManager\Application\UseCases\DeleteMenuTypeUseCase;
use App\Modules\MenuManager\Application\UseCases\UpdateMenuTypeOrderingUseCase;
use App\Modules\MenuManager\Application\UseCases\UpdateMenuTypeStatusUseCase;
use App\Modules\MenuManager\Application\UseCases\UpdateMenuTypeUseCase;
use App\Modules\MenuManager\Infrastructure\Http\Requests\CreateMenuTypeRequest;
use App\Modules\MenuManager\Infrastructure\Http\Requests\UpdateMenuTypeRequest;
use App\Modules\MenuManager\Infrastructure\Http\Requests\UpdateOrderingRequest;
use App\Modules\MenuManager\Infrastructure\Http\Requests\UpdateStatusRequest;
use App\Modules\MenuManager\Infrastructure\Http\Resources\MenuTypeResource;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class AdminMenuTypeCommandController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private CreateMenuTypeUseCase $createUseCase,
        private UpdateMenuTypeUseCase $updateUseCase,
        private DeleteMenuTypeUseCase $deleteUseCase,
        private UpdateMenuTypeOrderingUseCase $updateOrderingUseCase,
        private UpdateMenuTypeStatusUseCase $updateStatusUseCase,
    ) {}

    public function store(CreateMenuTypeRequest $request): JsonResponse
    {
        $this->authorize('create', MenuTypeModel::class);

        $data = new CreateMenuTypeData(
            title: $request->input('title'),
            alias: $request->input('alias'),
            description: $request->input('description'),
            ordering: $request->input('ordering', 0),
            status: $request->boolean('status', true),
        );
        $menuType = $this->createUseCase->execute($data);
        return response()->json(['data' => new MenuTypeResource($menuType)]);
    }

    public function update(UpdateMenuTypeRequest $request, int $id): JsonResponse
    {
        $menuType = MenuTypeModel::find($id);
        if (! $menuType) abort(404);
        $this->authorize('update', $menuType);

        $data = new UpdateMenuTypeData(
            title: $request->input('title'),
            alias: $request->input('alias'),
            description: $request->input('description'),
            ordering: $request->input('ordering'),
            status: $request->has('status') ? $request->boolean('status') : null,
        );
        $menuType = $this->updateUseCase->execute($id, $data);
        return response()->json(['data' => new MenuTypeResource($menuType)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $menuType = MenuTypeModel::find($id);
        if (! $menuType) abort(404);
        $this->authorize('delete', $menuType);

        $deleted = $this->deleteUseCase->execute($id);
        if (! $deleted) return response()->json(['message' => 'Menu type not found'], 404);
        return response()->json(['message' => 'Deleted successfully'], 200);
    }

    public function updateOrdering(UpdateOrderingRequest $request): JsonResponse
    {
        $this->authorize('update', MenuTypeModel::class);

        $this->updateOrderingUseCase->execute($request->input('order'));
        return response()->json(['message' => 'Ordering updated successfully']);
    }

    public function updateStatus(UpdateStatusRequest $request, int $id): JsonResponse
    {
        $menuType = MenuTypeModel::find($id);
        if (! $menuType) abort(404);
        $this->authorize('update', $menuType);

        $updated = $this->updateStatusUseCase->execute($id, $request->boolean('status'));
        if (! $updated) return response()->json(['message' => 'Menu type not found'], 404);
        return response()->json(['message' => 'Статус обновлен', 'status' => $request->boolean('status')]);
    }
}
