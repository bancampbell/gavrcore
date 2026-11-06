<?php

namespace App\Modules\MaterialManager\Infrastructure\Http\Controllers;

use App\Modules\MaterialManager\Application\DTO\BulkActionData;
use App\Modules\MaterialManager\Application\DTO\CreateMaterialData;
use App\Modules\MaterialManager\Application\DTO\UpdateMaterialData;
use App\Modules\MaterialManager\Application\UseCases\BulkDeleteUseCase;
use App\Modules\MaterialManager\Application\UseCases\BulkForceDeleteUseCase;
use App\Modules\MaterialManager\Application\UseCases\BulkPublishUseCase;
use App\Modules\MaterialManager\Application\UseCases\BulkRestoreUseCase;
use App\Modules\MaterialManager\Application\UseCases\BulkUnpublishUseCase;
use App\Modules\MaterialManager\Application\UseCases\CreateMaterialUseCase;
use App\Modules\MaterialManager\Application\UseCases\DeleteMaterialUseCase;
use App\Modules\MaterialManager\Application\UseCases\ForceDeleteMaterialUseCase;
use App\Modules\MaterialManager\Application\UseCases\GetMaterialForEditUseCase;
use App\Modules\MaterialManager\Application\UseCases\GetTrashMaterialsUseCase;
use App\Modules\MaterialManager\Application\UseCases\PublishMaterialUseCase;
use App\Modules\MaterialManager\Application\UseCases\RestoreMaterialUseCase;
use App\Modules\MaterialManager\Application\UseCases\UnpublishMaterialUseCase;
use App\Modules\MaterialManager\Application\UseCases\UpdateMaterialUseCase;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Infrastructure\Http\Requests\BulkActionRequest;
use App\Modules\MaterialManager\Infrastructure\Http\Requests\CreateMaterialRequest;
use App\Modules\MaterialManager\Infrastructure\Http\Requests\UpdateMaterialRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class AdminMaterialCommandController
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CreateMaterialUseCase $createMaterial,
        private readonly UpdateMaterialUseCase $updateMaterial,
        private readonly DeleteMaterialUseCase $deleteMaterial,
        private readonly RestoreMaterialUseCase $restoreMaterial,
        private readonly ForceDeleteMaterialUseCase $forceDeleteMaterial,
        private readonly PublishMaterialUseCase $publishMaterial,
        private readonly UnpublishMaterialUseCase $unpublishMaterial,
        private readonly BulkDeleteUseCase $bulkDelete,
        private readonly BulkRestoreUseCase $bulkRestore,
        private readonly BulkForceDeleteUseCase $bulkForceDelete,
        private readonly BulkPublishUseCase $bulkPublish,
        private readonly BulkUnpublishUseCase $bulkUnpublish,
        private readonly GetMaterialForEditUseCase $getMaterialForEdit,
        private readonly GetTrashMaterialsUseCase $getTrashMaterials,
        private readonly MaterialRepositoryInterface $repository,
    ) {}

    public function store(CreateMaterialRequest $request): RedirectResponse|JsonResponse
    {
        $data = CreateMaterialData::fromArray($request->validated(), auth()->id());

        try {
            $material = $this->createMaterial->execute($data, auth()->id());
        } catch (\DomainException $e) {
            // Конфликт явно переданного слага (и прочие доменные отказы) —
            // это 422, а не 500
            throw ValidationException::withMessages(['slug' => $e->getMessage()]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Материал создан',
                'id' => $material->id,
            ], 201);
        }

        return redirect()->route('admin.materials.index')
            ->with('success', 'Материал создан');
    }

    public function update(UpdateMaterialRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $material = $this->getMaterialForEdit->execute($id);
        $this->authorize('update', $material);

        $data = UpdateMaterialData::fromArray($request->validated());

        try {
            $updated = $this->updateMaterial->execute($id, $data, auth()->id());
        } catch (\DomainException $e) {
            // Конфликт слага / пустой slug — доменный отказ, 422 вместо 500.
            // «Материал не найден» здесь больше не прилетает: use case
            // бросает ModelNotFoundException → 404.
            throw ValidationException::withMessages(['slug' => $e->getMessage()]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Материал обновлён',
            ]);
        }

        return redirect()->route('admin.materials.index')
            ->with('success', 'Материал обновлён');
    }

    public function destroy(int $id): RedirectResponse|JsonResponse
    {
        $material = $this->repository->findById($id);

        if (! $material) {
            throw (new ModelNotFoundException())->setModel(Material::class, [$id]);
        }

        $this->authorize('moveToTrash', $material);

        try {
            $this->deleteMaterial->execute($id, auth()->id());
        } catch (\DomainException $e) {
            // Гонка состояний (материал уже в корзине и т.п.) — 422, не 500
            if (request()->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return redirect()->route('admin.materials.index')
                ->withErrors(['material' => $e->getMessage()]);
        }

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Материал перемещён в корзину']);
        }

        return redirect()->route('admin.materials.index')
            ->with('success', 'Материал перемещён в корзину');
    }

    public function bulkTrash(BulkActionRequest $request): JsonResponse
    {
        $data = BulkActionData::fromRequest($request->validated());
        $this->authorizeBulk($data->ids, 'moveToTrash');

        $count = $this->bulkDelete->execute($data->ids, auth()->id());

        $message = $count === 1
            ? 'Материал перемещён в корзину'
            : 'Материалы перемещены в корзину';

        return response()->json(['message' => $message]);
    }

    public function restore(BulkActionRequest $request): JsonResponse
    {
        $data = BulkActionData::fromRequest($request->validated());
        $this->authorizeBulk($data->ids, 'restore');

        $count = $this->bulkRestore->execute($data->ids, auth()->id());

        $message = $count === 1
            ? 'Материал восстановлен'
            : 'Материалы восстановлены';

        return response()->json(['message' => $message]);
    }

    public function forceDelete(BulkActionRequest $request): JsonResponse
    {
        $data = BulkActionData::fromRequest($request->validated());
        $this->authorizeBulk($data->ids, 'forceDelete');

        $count = $this->bulkForceDelete->execute($data->ids, auth()->id());

        $message = $count === 1
            ? 'Материал удалён навсегда'
            : 'Материалы удалены навсегда';

        return response()->json(['message' => $message]);
    }

    public function emptyTrash(): JsonResponse
    {
        $this->authorize('forceDelete', Material::class);

        $ids = $this->repository->getAllTrashIds();
        $count = count($ids);

        if ($count > 0) {
            $this->bulkForceDelete->execute($ids, auth()->id());
        }

        return response()->json([
            'message' => "Корзина очищена, удалено {$count} материалов",
        ]);
    }

    public function bulkPublish(BulkActionRequest $request): JsonResponse
    {
        $data = BulkActionData::fromRequest($request->validated());
        $this->authorizeBulk($data->ids, 'publish');

        $count = $this->bulkPublish->execute($data->ids, auth()->id());

        $message = $count === 1
            ? 'Материал опубликован'
            : 'Материалы опубликованы';

        return response()->json(['message' => $message]);
    }

    public function bulkUnpublish(BulkActionRequest $request): JsonResponse
    {
        $data = BulkActionData::fromRequest($request->validated());
        $this->authorizeBulk($data->ids, 'unpublish');

        $count = $this->bulkUnpublish->execute($data->ids, auth()->id());

        $message = $count === 1
            ? 'Материал снят с публикации'
            : 'Материалы сняты с публикации';

        return response()->json(['message' => $message]);
    }

    private function authorizeBulk(array $ids, string $ability): void
    {
        $materials = $this->repository->findMany($ids);
        foreach ($materials as $material) {
            $this->authorize($ability, $material);
        }
    }
}
