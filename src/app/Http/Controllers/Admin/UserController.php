<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\BulkStatusRequest;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Requests\Admin\User\UserIndexRequest;
use App\Models\Group;
use App\Models\User;
use App\Services\UserService;
use Inertia\Response;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected UserService $userService
    ) {}

    public function index(UserIndexRequest $request): Response
    {
        $this->authorize('viewAny', User::class);

        $filters = $request->validated();
        $users = $this->userService->getPaginated($filters);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'groups' => Group::all(),
            'filters' => $filters,
            'user' => auth()->user(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Admin/Users/Create', [
            'groups' => Group::all(),
            'user' => auth()->user(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = $this->userService->create($request->validated());

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь создан');
    }

    public function edit(int $id): Response
    {
        $user = $this->userService->find($id);
        $this->authorize('update', $user);

        return Inertia::render('Admin/Users/Edit', [
            'editUser' => $user,
            'groups' => Group::all(),
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateUserRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $user = $this->userService->find($id);
        $this->authorize('update', $user);

        $user = $this->userService->update($id, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Пользователь обновлён']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Пользователь обновлён');
    }

    public function destroy(int $id): JsonResponse
    {
        $user = $this->userService->find($id);
        $this->authorize('delete', $user);

        $this->userService->delete($id);
        return response()->json(['message' => 'Пользователь удалён']);
    }

    public function bulkBlock(BulkStatusRequest $request): JsonResponse
    {
        $this->authorize('bulkBlock', User::class);

        $count = 0;
        foreach ($request->ids as $id) {
            $user = $this->userService->find($id);
            if ($user && $this->userService->updateStatus($id, true)) {
                $count++;
            }
        }

        $message = $count === 1 ? 'Пользователь заблокирован' : 'Пользователи заблокированы';
        return response()->json(['message' => $message]);
    }

    public function bulkUnblock(BulkStatusRequest $request): JsonResponse
    {
        $this->authorize('bulkUnblock', User::class);

        $count = 0;
        foreach ($request->ids as $id) {
            $user = $this->userService->find($id);
            if ($user && $this->userService->updateStatus($id, false)) {
                $count++;
            }
        }

        $message = $count === 1 ? 'Пользователь разблокирован' : 'Пользователи разблокированы';
        return response()->json(['message' => $message]);
    }
}
