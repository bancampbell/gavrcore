<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Group\GroupIndexRequest;
use App\Http\Requests\Admin\Group\StoreGroupRequest;
use App\Http\Requests\Admin\Group\UpdateGroupRequest;
use App\Services\GroupService;
use Inertia\Inertia;

class GroupController extends Controller
{
    public function __construct(
        protected GroupService $groupService
    ) {}

    public function index(GroupIndexRequest $request)
    {
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

    public function create()
    {
        return Inertia::render('Admin/Groups/Create', [
            'user' => auth()->user(),
        ]);
    }

    public function store(StoreGroupRequest $request)
    {
        $this->groupService->create($request->validated());

        return redirect()->route('admin.groups.index')
            ->with('success', 'Группа создана');
    }

    public function edit(int $id)
    {
        $group = $this->groupService->find($id);

        return Inertia::render('Admin/Groups/Edit', [
            'editGroup' => $group,
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateGroupRequest $request, int $id)
    {
        $this->groupService->update($id, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Группа обновлена']);
        }

        return redirect()->route('admin.groups.index')->with('success', 'Группа обновлена');
    }

    public function destroy(int $id)
    {
        $this->groupService->delete($id);
        return response()->json(['message' => 'Группа удалена']);
    }

    public function updateStatus(int $id)
    {
        $group = $this->groupService->find($id);
        $newStatus = !$group->status;
        $this->groupService->updateStatus($id, $newStatus);

        return response()->json(['message' => 'Статус обновлён']);
    }
}
