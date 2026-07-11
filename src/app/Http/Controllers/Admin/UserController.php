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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Facades\Activity;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected UserService $userService
    ) {
    }

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
            'title' => 'Пользователи',
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Admin/Users/Create', [
            'groups' => Group::all(),
            'user' => auth()->user(),
            'title' => 'Создать пользователя',
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = $this->userService->create($request->validated());

        // Логируем создание пользователя
        Activity::causedBy(auth()->user())
            ->performedOn($user)
            ->log('Создан пользователь: ' . $user->name);

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
            'title' => 'Редактировать пользователя',
        ]);
    }

    public function update(UpdateUserRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $user = $this->userService->find($id);
        $this->authorize('update', $user);

        // Сохраняем старые данные для логирования
        $oldData = $user->only(['name', 'username', 'email', 'blocked', 'activated']);

        $this->userService->update($id, $request->validated());

        // Получаем обновленные данные
        $user->refresh();
        $newData = $user->only(['name', 'username', 'email', 'blocked', 'activated']);

        // Логируем обновление пользователя с изменениями
        Activity::causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'old' => $oldData,
                'attributes' => $newData,
            ])
            ->log('Обновлен пользователь: ' . $user->name);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Пользователь обновлён']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Пользователь обновлён');
    }

    public function destroy(int $id): JsonResponse
    {
        $user = $this->userService->find($id);
        $this->authorize('delete', $user);

        $userName = $user->name;

        $this->userService->delete($id);

        // Логируем удаление пользователя
        Activity::causedBy(auth()->user())
            ->log('Удален пользователь: ' . $userName);

        return response()->json(['message' => 'Пользователь удалён']);
    }

    public function bulkBlock(BulkStatusRequest $request): JsonResponse
    {
        $this->authorize('bulkBlock', User::class);

        $count = 0;
        $userNames = [];
        foreach ($request->ids as $id) {
            $user = $this->userService->find($id);
            if ($user && $this->userService->updateStatus($id, true)) {
                $count++;
                $userNames[] = $user->name;
            }
        }

        $message = $count === 1 ? 'Пользователь заблокирован' : 'Пользователи заблокированы';

        // Логируем массовую блокировку
        Activity::causedBy(auth()->user())
            ->withProperties(['users' => $userNames, 'count' => $count])
            ->log('Заблокировано пользователей: ' . $count);

        return response()->json(['message' => $message]);
    }

    public function bulkUnblock(BulkStatusRequest $request): JsonResponse
    {
        $this->authorize('bulkUnblock', User::class);

        $count = 0;
        $userNames = [];
        foreach ($request->ids as $id) {
            $user = $this->userService->find($id);
            if ($user && $this->userService->updateStatus($id, false)) {
                $count++;
                $userNames[] = $user->name;
            }
        }

        $message = $count === 1 ? 'Пользователь разблокирован' : 'Пользователи разблокированы';

        // Логируем массовую разблокировку
        Activity::causedBy(auth()->user())
            ->withProperties(['users' => $userNames, 'count' => $count])
            ->log('Разблокировано пользователей: ' . $count);

        return response()->json(['message' => $message]);
    }
}
