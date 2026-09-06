<?php

namespace App\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserManager\Application\DTO\UserFiltersData;
use App\Modules\UserManager\Application\UseCases\GetAllGroupsUseCase;
use App\Modules\UserManager\Application\UseCases\GetUserByIdUseCase;
use App\Modules\UserManager\Application\UseCases\GetUserListUseCase;
use App\Modules\UserManager\Domain\Entities\User;
use App\Modules\UserManager\Domain\ValueObjects\UserId;
use App\Modules\UserManager\Infrastructure\Http\Requests\Admin\UserIndexRequest;
use App\Modules\UserManager\Infrastructure\Http\Resources\GroupResource;
use App\Modules\UserManager\Infrastructure\Http\Resources\UserResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

final class AdminUserQueryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly GetUserListUseCase $getUserList,
        private readonly GetUserByIdUseCase $getUserById,
        private readonly GetAllGroupsUseCase $getAllGroups,
    ) {
    }

    public function index(UserIndexRequest $request): Response
    {
        $this->authorize('viewAny', User::class);

        $filters = UserFiltersData::fromArray($request->validated());

        // Пагинатор остаётся ресурсом: Inertia разрешает его в {data, links, meta},
        // фронт читает pagination из users.meta.*
        return Inertia::render('UserManager/Users/Index', [
            'users' => UserResource::collection($this->getUserList->execute($filters)),
            'groups' => GroupResource::collection($this->getAllGroups->execute())->resolve(),
            'filters' => $request->only(['search', 'blocked', 'activated']),
            'user' => auth()->user(),
            'title' => 'Пользователи',
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('UserManager/Users/Create', [
            'groups' => GroupResource::collection($this->getAllGroups->execute())->resolve(),
            'user' => auth()->user(),
            'title' => 'Создать пользователя',
        ]);
    }

    public function edit(int $id): Response
    {
        $user = $this->getUserById->execute(UserId::fromInt($id));
        abort_if(! $user, 404);

        $this->authorize('update', $user);

        return Inertia::render('UserManager/Users/Edit', [
            'editUser' => (new UserResource($user))->resolve(),
            'groups' => GroupResource::collection($this->getAllGroups->execute())->resolve(),
            'user' => auth()->user(),
            'title' => 'Редактировать пользователя',
        ]);
    }
}
