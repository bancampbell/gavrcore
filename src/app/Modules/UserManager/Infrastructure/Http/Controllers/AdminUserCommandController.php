<?php

namespace App\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserManager\Application\DTO\CreateUserData;
use App\Modules\UserManager\Application\DTO\UpdateUserData;
use App\Modules\UserManager\Application\UseCases\CreateUserUseCase;
use App\Modules\UserManager\Application\UseCases\DeleteUserUseCase;
use App\Modules\UserManager\Application\UseCases\GetUserByIdUseCase;
use App\Modules\UserManager\Application\UseCases\UpdateUserStatusUseCase;
use App\Modules\UserManager\Application\UseCases\UpdateUserUseCase;
use App\Modules\UserManager\Domain\Entities\User;
use App\Modules\UserManager\Domain\ValueObjects\UserId;
use App\Modules\UserManager\Infrastructure\Http\Requests\Admin\BulkStatusRequest;
use App\Modules\UserManager\Infrastructure\Http\Requests\Admin\StoreUserRequest;
use App\Modules\UserManager\Infrastructure\Http\Requests\Admin\UpdateUserRequest;
use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\Activity;

final class AdminUserCommandController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CreateUserUseCase $createUser,
        private readonly UpdateUserUseCase $updateUser,
        private readonly DeleteUserUseCase $deleteUser,
        private readonly GetUserByIdUseCase $getUserById,
        private readonly UpdateUserStatusUseCase $updateUserStatus,
    ) {
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = $this->createUser->execute(CreateUserData::fromArray($request->validated()));

        Activity::causedBy(auth()->user())
            ->withProperties(['user_id' => $user->id?->value])
            ->log('Создан пользователь: '.$user->name);

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь создан');
    }

    public function update(UpdateUserRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $userId = UserId::fromInt($id);
        $user = $this->getUserById->execute($userId);
        abort_if(! $user, 404);

        $this->authorize('update', $user);

        $data = UpdateUserData::fromArray($request->validated());

        // Параллельно с bulk-block: нельзя заблокировать/деактивировать самого
        // себя — иначе последний админ выводит себя из строя безвозвратно.
        if ($userId->value === auth()->id()) {
            $selfBlock = $request->has('blocked') && $request->boolean('blocked');
            $selfDeactivate = $request->has('activated') && ! $request->boolean('activated');

            if ($selfBlock || $selfDeactivate) {
                throw ValidationException::withMessages([
                    'blocked' => ['Нельзя заблокировать или деактивировать самого себя'],
                ]);
            }

            // Третий способ той же потери доступа: снятие с себя групп,
            // дающих admin.access. Проверяем только когда группы реально
            // передаются и пользователь сейчас держит admin.access.
            if ($data->groupIds->isPresent()
                && $this->wouldLoseAdminAccess($userId->value, $data->groupIds->value())) {
                throw ValidationException::withMessages([
                    'groups' => ['Нельзя лишить самого себя прав администратора'],
                ]);
            }
        }

        $oldData = [
            'name' => $user->name,
            'username' => $user->username->value,
            'email' => $user->email->value,
            'blocked' => $user->blocked,
            'activated' => $user->activated,
        ];

        $updated = $this->updateUser->execute($userId, $data);

        Activity::causedBy(auth()->user())
            ->withProperties([
                'user_id' => $updated->id?->value,
                'old' => $oldData,
                'attributes' => [
                    'name' => $updated->name,
                    'username' => $updated->username->value,
                    'email' => $updated->email->value,
                    'blocked' => $updated->blocked,
                    'activated' => $updated->activated,
                ],
            ])
            ->log('Обновлен пользователь: '.$updated->name);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Пользователь обновлён']);
        }

        return redirect()->route('admin.users.index')->with('success', 'Пользователь обновлён');
    }

    public function destroy(int $id): JsonResponse
    {
        $userId = UserId::fromInt($id);
        $user = $this->getUserById->execute($userId);
        abort_if(! $user, 404);

        $this->authorize('delete', $user);

        $userName = $user->name;

        $this->deleteUser->execute($userId);

        Activity::causedBy(auth()->user())
            ->log('Удален пользователь: '.$userName);

        return response()->json(['message' => 'Пользователь удалён']);
    }

    public function bulkBlock(BulkStatusRequest $request): JsonResponse
    {
        $this->authorize('bulkBlock', User::class);

        // Приводим id к int ДО сравнения: form-encoded тело (ids[]=1) и JSON
        // со строками ({"ids":["1"]}) дают string, и строгое сравнение
        // пропускало собственный id — админ блокировал сам себя.
        $ids = array_map('intval', $request->ids);
        $currentUserId = (int) auth()->id();

        if (in_array($currentUserId, $ids, true)) {
            throw ValidationException::withMessages([
                'ids' => ['Нельзя заблокировать самого себя'],
            ]);
        }

        $result = $this->updateUserStatus->execute($ids, true, $currentUserId);

        $message = $result['count'] === 1 ? 'Пользователь заблокирован' : 'Пользователи заблокированы';

        Activity::causedBy(auth()->user())
            ->withProperties(['users' => $result['names'], 'count' => $result['count']])
            ->log('Заблокировано пользователей: '.$result['count']);

        return response()->json(['message' => $message]);
    }

    public function bulkUnblock(BulkStatusRequest $request): JsonResponse
    {
        $this->authorize('bulkUnblock', User::class);

        $result = $this->updateUserStatus->execute(
            array_map('intval', $request->ids),
            false,
            (int) auth()->id()
        );

        $message = $result['count'] === 1 ? 'Пользователь разблокирован' : 'Пользователи разблокированы';

        Activity::causedBy(auth()->user())
            ->withProperties(['users' => $result['names'], 'count' => $result['count']])
            ->log('Разблокировано пользователей: '.$result['count']);

        return response()->json(['message' => $message]);
    }

    /**
     * Станет ли пользователь без admin.access после sync групп на $newGroupIds.
     *
     * Учитывает оба источника права: группы с permission admin.access и
     * прямые user_permissions. Если право и так отсутствует — терять нечего.
     *
     * @param  array<int, int>  $newGroupIds
     */
    private function wouldLoseAdminAccess(int $userId, array $newGroupIds): bool
    {
        /** @var UserModel|null $model */
        $model = UserModel::with(['permissions', 'groups.permissions'])->find($userId);

        if (! $model || ! $model->hasPermission('admin.access')) {
            return false;
        }

        $adminGroupIds = $model->groups
            ->filter(fn ($group) => $group->permissions->contains('key', 'admin.access'))
            ->pluck('id')
            ->all();

        $keepsAdminGroup = count(array_intersect($adminGroupIds, $newGroupIds)) > 0;
        $hasDirectAdminPermission = $model->permissions->contains('key', 'admin.access');

        return ! $keepsAdminGroup && ! $hasDirectAdminPermission;
    }
}
