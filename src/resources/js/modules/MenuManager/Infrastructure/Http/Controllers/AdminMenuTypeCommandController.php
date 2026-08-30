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
use Illuminate\Http\JsonResponse;

class AdminMenuTypeCommandController extends Controller
{
    public function __construct(
        private CreateMenuTypeUseCase $createUseCase,
        private UpdateMenuTypeUseCase $updateUseCase,
        private DeleteMenuTypeUseCase $deleteUseCase,
        private UpdateMenuTypeOrderingUseCase $updateOrderingUseCase,
        private UpdateMenuTypeStatusUseCase $updateStatusUseCase,
    ) {}

    public function store(CreateMenuTypeRequest $request): MenuTypeResource
    {
        $data = new CreateMenuTypeData(
            title: $request->input('title'),
            alias: $request->input('alias'),
            description: $request->input('description'),
            ordering: $request->input('ordering', 0),
            status: $request->boolean('status', true),
        );
        $menuType = $this->createUseCase->execute($data);
        return new MenuTypeResource($menuType);
    }

    public function update(UpdateMenuTypeRequest $request, int $id): MenuTypeResource
    {
        $data = new UpdateMenuTypeData(
            title: $request->input('title'),
            alias: $request->input('alias'),
            description: $request->input('description'),
            ordering: $request->input('ordering'),
            status: $request->has('status') ? $request->boolean('status') : null,
        );
        $menuType = $this->updateUseCase->execute($id, $data);
        return new MenuTypeResource($menuType);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->deleteUseCase->execute($id);
        if (! $deleted) return response()->json(['message' => 'Menu type not found'], 404);
        return response()->json(['message' => 'Deleted successfully'], 200);
    }

    public function updateOrdering(UpdateOrderingRequest $request): JsonResponse
    {
        $this->updateOrderingUseCase->execute($request->input('order'));
        return response()->json(['message' => 'Ordering updated successfully']);
    }

    public function updateStatus(UpdateStatusRequest $request, int $id): JsonResponse
    {
        $updated = $this->updateStatusUseCase->execute($id, $request->boolean('status'));
        if (! $updated) return response()->json(['message' => 'Menu type not found'], 404);
        return response()->json(['message' => 'Статус обновлен', 'status' => $request->boolean('status')]);
    }
}