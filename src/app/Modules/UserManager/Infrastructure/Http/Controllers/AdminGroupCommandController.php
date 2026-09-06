<?php

namespace App\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserManager\Application\DTO\CreateGroupData;
use App\Modules\UserManager\Application\DTO\UpdateGroupData;
use App\Modules\UserManager\Application\UseCases\CreateGroupUseCase;
use App\Modules\UserManager\Application\UseCases\DeleteGroupUseCase;
use App\Modules\UserManager\Application\UseCases\GetGroupByIdUseCase;
use App\Modules\UserManager\Application\UseCases\UpdateGroupStatusUseCase;
use App\Modules\UserManager\Application\UseCases\UpdateGroupUseCase;
use App\Modules\UserManager\Domain\Entities\Group;
use App\Modules\UserManager\Domain\ValueObjects\GroupId;
use App\Modules\UserManager\Infrastructure\Http\Requests\Admin\StoreGroupRequest;
use App\Modules\UserManager\Infrastructure\Http\Requests\Admin\UpdateGroupRequest;
use App\Modules\UserManager\Infrastructure\Http\Requests\Admin\UpdateGroupStatusRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

final class AdminGroupCommandController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CreateGroupUseCase $createGroup,
        private readonly UpdateGroupUseCase $updateGroup,
        private readonly DeleteGroupUseCase $deleteGroup,
        private readonly GetGroupByIdUseCase $getGroupById,
        private readonly UpdateGroupStatusUseCase $updateGroupStatus,
    ) {
    }

    public function store(StoreGroupRequest $request): RedirectResponse
    {
        $this->authorize('create', Group::class);

        $this->createGroup->execute(CreateGroupData::fromArray($request->validated()));

        return redirect()->route('admin.groups.index')
            ->with('success', 'Группа создана');
    }

    public function update(UpdateGroupRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $groupId = GroupId::fromInt($id);
        $group = $this->getGroupById->execute($groupId);
        abort_if(! $group, 404);

        $this->authorize('update', $group);

        $this->updateGroup->execute($groupId, UpdateGroupData::fromArray($request->validated()));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Группа обновлена']);
        }

        return redirect()->route('admin.groups.index')->with('success', 'Группа обновлена');
    }

    public function destroy(int $id): JsonResponse
    {
        $groupId = GroupId::fromInt($id);
        $group = $this->getGroupById->execute($groupId);
        abort_if(! $group, 404);

        $this->authorize('delete', $group);

        $this->deleteGroup->execute($groupId);

        return response()->json(['message' => 'Группа удалена']);
    }

    public function updateStatus(UpdateGroupStatusRequest $request, int $id): JsonResponse
    {
        $groupId = GroupId::fromInt($id);
        $group = $this->getGroupById->execute($groupId);
        abort_if(! $group, 404);

        $this->authorize('update', $group);

        $status = $request->boolean('status');

        $this->updateGroupStatus->execute($groupId, $status);

        return response()->json([
            'message' => 'Статус обновлён',
            'status' => $status,
        ]);
    }
}
