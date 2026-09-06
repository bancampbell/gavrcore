<?php

namespace App\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserManager\Application\DTO\CreateAccessLevelData;
use App\Modules\UserManager\Application\DTO\UpdateAccessLevelData;
use App\Modules\UserManager\Application\UseCases\CreateAccessLevelUseCase;
use App\Modules\UserManager\Application\UseCases\DeleteAccessLevelUseCase;
use App\Modules\UserManager\Application\UseCases\GetAccessLevelByIdUseCase;
use App\Modules\UserManager\Application\UseCases\UpdateAccessLevelOrderingUseCase;
use App\Modules\UserManager\Application\UseCases\UpdateAccessLevelStatusUseCase;
use App\Modules\UserManager\Application\UseCases\UpdateAccessLevelUseCase;
use App\Modules\UserManager\Domain\Entities\AccessLevel;
use App\Modules\UserManager\Domain\ValueObjects\AccessLevelId;
use App\Modules\UserManager\Infrastructure\Http\Requests\Admin\StoreAccessLevelRequest;
use App\Modules\UserManager\Infrastructure\Http\Requests\Admin\UpdateAccessLevelRequest;
use App\Modules\UserManager\Infrastructure\Http\Requests\Admin\UpdateAccessLevelStatusRequest;
use App\Modules\UserManager\Infrastructure\Http\Requests\Admin\UpdateOrderingRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

final class AdminAccessLevelCommandController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CreateAccessLevelUseCase $createAccessLevel,
        private readonly UpdateAccessLevelUseCase $updateAccessLevel,
        private readonly DeleteAccessLevelUseCase $deleteAccessLevel,
        private readonly GetAccessLevelByIdUseCase $getAccessLevelById,
        private readonly UpdateAccessLevelStatusUseCase $updateAccessLevelStatus,
        private readonly UpdateAccessLevelOrderingUseCase $updateAccessLevelOrdering,
    ) {
    }

    public function store(StoreAccessLevelRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', AccessLevel::class);

        $this->createAccessLevel->execute(CreateAccessLevelData::fromArray($request->validated()));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Уровень доступа создан']);
        }

        return redirect()->route('admin.access-levels.index')
            ->with('success', 'Уровень доступа создан');
    }

    public function update(UpdateAccessLevelRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $accessLevelId = AccessLevelId::fromInt($id);
        $accessLevel = $this->getAccessLevelById->execute($accessLevelId);
        abort_if(! $accessLevel, 404);

        $this->authorize('update', $accessLevel);

        $this->updateAccessLevel->execute($accessLevelId, UpdateAccessLevelData::fromArray($request->validated()));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Уровень доступа обновлён']);
        }

        return redirect()->route('admin.access-levels.index')
            ->with('success', 'Уровень доступа обновлён');
    }

    public function destroy(int $id): JsonResponse
    {
        $accessLevelId = AccessLevelId::fromInt($id);
        $accessLevel = $this->getAccessLevelById->execute($accessLevelId);
        abort_if(! $accessLevel, 404);

        $this->authorize('delete', $accessLevel);

        $this->deleteAccessLevel->execute($accessLevelId);

        return response()->json(['message' => 'Уровень доступа удалён']);
    }

    public function updateOrdering(UpdateOrderingRequest $request): JsonResponse
    {
        $this->authorize('updateOrdering', AccessLevel::class);

        $this->updateAccessLevelOrdering->execute($request->order);

        return response()->json(['message' => 'Порядок обновлён']);
    }

    public function updateStatus(UpdateAccessLevelStatusRequest $request, int $id): JsonResponse
    {
        $accessLevelId = AccessLevelId::fromInt($id);
        $accessLevel = $this->getAccessLevelById->execute($accessLevelId);
        abort_if(! $accessLevel, 404);

        $this->authorize('update', $accessLevel);

        $status = $request->boolean('status');

        $this->updateAccessLevelStatus->execute($accessLevelId, $status);

        return response()->json([
            'message' => 'Статус обновлён',
            'status' => $status,
        ]);
    }
}
