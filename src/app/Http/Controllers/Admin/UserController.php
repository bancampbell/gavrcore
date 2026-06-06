<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\BulkStatusRequest;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Requests\Admin\User\UserIndexRequest;
use App\Models\Group;
use App\Services\UserService;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(UserIndexRequest $request)
    {
        $filters = $request->validated();
        $users = $this->userService->getPaginated($filters);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'groups' => Group::all(),
            'filters' => $filters,
            'user' => auth()->user(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'groups' => Group::all(),
            'user' => auth()->user(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->create($request->validated());

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь создан');
    }

    public function edit(int $id)
    {
        $user = $this->userService->find($id);

        return Inertia::render('Admin/Users/Edit', [
            'editUser' => $user,
            'groups' => Group::all(),
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateUserRequest $request, int $id)
    {
        $user = $this->userService->update($id, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Пользователь обновлён']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Пользователь обновлён');
    }

    public function destroy(int $id)
    {
        $this->userService->delete($id);
        return response()->json(['message' => 'Пользователь удалён']);
    }

    public function bulkBlock(BulkStatusRequest $request)
    {
        $count = 0;
        foreach ($request->ids as $id) {
            if ($this->userService->updateStatus($id, true)) {
                $count++;
            }
        }

        $message = $count === 1 ? 'Пользователь заблокирован' : 'Пользователи заблокированы';
        return response()->json(['message' => $message]);
    }

    public function bulkUnblock(BulkStatusRequest $request)
    {
        $count = 0;
        foreach ($request->ids as $id) {
            if ($this->userService->updateStatus($id, false)) {
                $count++;
            }
        }

        $message = $count === 1 ? 'Пользователь разблокирован' : 'Пользователи разблокированы';
        return response()->json(['message' => $message]);
    }
}
