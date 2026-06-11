<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Group\GroupIndexRequest;
use App\Http\Requests\Admin\Group\StoreGroupRequest;
use App\Http\Requests\Admin\Group\UpdateGroupRequest;
use App\Models\Group;
use App\Models\Permission;
use App\Services\GroupService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected GroupService $groupService
    ) {
    }

    public function index(GroupIndexRequest $request): Response|JsonResponse
    {
        $this->authorize('viewAny', Group::class);

        $filters = $request->validated();
        $perPage = $request->get('per_page', 20);

        $groups = $this->groupService->getPaginated($filters, $perPage);

        if ($request->wantsJson()) {
            return response()->json($groups);
        }

        return Inertia::render('Admin/Groups/Index', [
            'groups' => $groups,
            'filters' => $filters,
            'user' => auth()->user(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Group::class);

        $permissions = Permission::orderBy('group')->orderBy('name')->get();

        return Inertia::render('Admin/Groups/Create', [
            'permissions' => $permissions,
            'user' => auth()->user(),
        ]);
    }

    public function store(StoreGroupRequest $request): RedirectResponse
    {
        $this->authorize('create', Group::class);

        $group = $this->groupService->create($request->validated());

        if ($request->has('permissions')) {
            $group->permissions()->sync($request->permissions);
        }

        return redirect()->route('admin.groups.index')
            ->with('success', 'Группа создана');
    }

    public function edit(int $id): Response
    {
        $group = $this->groupService->find($id);
        $this->authorize('update', $group);

        $permissions = Permission::orderBy('group')->orderBy('name')->get();
        $groupPermissions = $group->permissions->pluck('id')->toArray();

        return Inertia::render('Admin/Groups/Edit', [
            'editGroup' => $group,
            'permissions' => $permissions,
            'groupPermissions' => $groupPermissions,
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateGroupRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $group = $this->groupService->find($id);
        $this->authorize('update', $group);

        $group = $this->groupService->update($id, $request->validated());

        if ($request->has('permissions')) {
            $group->permissions()->sync($request->permissions);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Группа обновлена']);
        }

        return redirect()->route('admin.groups.index')->with('success', 'Группа обновлена');
    }

    public function destroy(int $id): JsonResponse
    {
        $group = $this->groupService->find($id);
        $this->authorize('delete', $group);

        $this->groupService->delete($id);

        return response()->json(['message' => 'Группа удалена']);
    }

    public function updateStatus(int $id): JsonResponse
    {
        $group = $this->groupService->find($id);
        $this->authorize('update', $group);

        $newStatus = ! $group->status;
        $this->groupService->updateStatus($id, $newStatus);

        return response()->json(['message' => 'Статус обновлён']);
    }
}
