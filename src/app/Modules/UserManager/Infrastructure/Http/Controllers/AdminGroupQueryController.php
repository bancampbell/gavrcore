<?php

namespace App\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserManager\Application\DTO\GroupFiltersData;
use App\Modules\UserManager\Application\UseCases\GetGroupByIdUseCase;
use App\Modules\UserManager\Application\UseCases\GetGroupListUseCase;
use App\Modules\UserManager\Application\UseCases\GetPermissionListUseCase;
use App\Modules\UserManager\Domain\Entities\Group;
use App\Modules\UserManager\Domain\ValueObjects\GroupId;
use App\Modules\UserManager\Infrastructure\Http\Requests\Admin\GroupIndexRequest;
use App\Modules\UserManager\Infrastructure\Http\Resources\GroupResource;
use App\Modules\UserManager\Infrastructure\Http\Resources\PermissionResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

final class AdminGroupQueryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly GetGroupListUseCase $getGroupList,
        private readonly GetGroupByIdUseCase $getGroupById,
        private readonly GetPermissionListUseCase $getPermissionList,
    ) {
    }

    public function index(GroupIndexRequest $request): Response|JsonResponse
    {
        $this->authorize('viewAny', Group::class);

        $filters = GroupFiltersData::fromArray($request->validated());
        $groups = $this->getGroupList->execute($filters);

        if ($request->wantsJson()) {
            return response()->json(GroupResource::collection($groups));
        }

        // Пагинатор остаётся ресурсом: фронт читает pagination из groups.meta.*
        return Inertia::render('UserManager/Groups/Index', [
            'groups' => GroupResource::collection($groups),
            'filters' => $request->only(['search', 'status']),
            'user' => auth()->user(),
            'title' => 'Группы пользователей',
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Group::class);

        return Inertia::render('UserManager/Groups/Create', [
            'permissions' => PermissionResource::collection($this->getPermissionList->execute())->resolve(),
            'user' => auth()->user(),
            'title' => 'Создать группу',
        ]);
    }

    public function edit(int $id): Response
    {
        $group = $this->getGroupById->execute(GroupId::fromInt($id));
        abort_if(! $group, 404);

        $this->authorize('update', $group);

        return Inertia::render('UserManager/Groups/Edit', [
            'editGroup' => (new GroupResource($group))->resolve(),
            'permissions' => PermissionResource::collection($this->getPermissionList->execute())->resolve(),
            'groupPermissions' => $group->permissionIds,
            'user' => auth()->user(),
            'title' => 'Редактировать группу',
        ]);
    }
}
