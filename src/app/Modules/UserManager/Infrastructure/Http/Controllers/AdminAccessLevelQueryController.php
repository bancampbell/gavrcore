<?php

namespace App\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserManager\Application\DTO\AccessLevelFiltersData;
use App\Modules\UserManager\Application\UseCases\GetAccessLevelByIdUseCase;
use App\Modules\UserManager\Application\UseCases\GetAccessLevelListUseCase;
use App\Modules\UserManager\Application\UseCases\GetAllGroupsUseCase;
use App\Modules\UserManager\Domain\Entities\AccessLevel;
use App\Modules\UserManager\Domain\ValueObjects\AccessLevelId;
use App\Modules\UserManager\Infrastructure\Http\Requests\Admin\AccessLevelIndexRequest;
use App\Modules\UserManager\Infrastructure\Http\Resources\AccessLevelResource;
use App\Modules\UserManager\Infrastructure\Http\Resources\GroupResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

final class AdminAccessLevelQueryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly GetAccessLevelListUseCase $getAccessLevelList,
        private readonly GetAccessLevelByIdUseCase $getAccessLevelById,
        private readonly GetAllGroupsUseCase $getAllGroups,
    ) {
    }

    public function index(AccessLevelIndexRequest $request): Response|JsonResponse
    {
        $this->authorize('viewAny', AccessLevel::class);

        $filters = AccessLevelFiltersData::fromArray($request->validated());
        $accessLevels = $this->getAccessLevelList->execute($filters);

        if ($request->wantsJson()) {
            return response()->json(AccessLevelResource::collection($accessLevels));
        }

        return Inertia::render('UserManager/AccessLevels/Index', [
            'accessLevels' => AccessLevelResource::collection($accessLevels)->resolve(),
            'groups' => GroupResource::collection($this->getAllGroups->execute())->resolve(),
            'filters' => $request->only(['search', 'status']),
            'user' => auth()->user(),
            'title' => 'Уровни доступа',
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', AccessLevel::class);

        return Inertia::render('UserManager/AccessLevels/Create', [
            'groups' => GroupResource::collection($this->getAllGroups->execute())->resolve(),
            'user' => auth()->user(),
            'title' => 'Создать уровень доступа',
        ]);
    }

    public function edit(int $id): Response
    {
        $accessLevel = $this->getAccessLevelById->execute(AccessLevelId::fromInt($id));
        abort_if(! $accessLevel, 404);

        $this->authorize('update', $accessLevel);

        return Inertia::render('UserManager/AccessLevels/Edit', [
            'editAccessLevel' => (new AccessLevelResource($accessLevel))->resolve(),
            'groups' => GroupResource::collection($this->getAllGroups->execute())->resolve(),
            'user' => auth()->user(),
            'title' => 'Редактировать уровень доступа',
        ]);
    }
}
