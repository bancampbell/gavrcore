<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessLevel;
use App\Models\Group;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AccessLevelController extends Controller
{
    use AuthorizesRequests;

    public function index(): Response
    {
        $this->authorize('viewAny', AccessLevel::class);

        $accessLevels = AccessLevel::with('groups')->orderBy('ordering')->get();

        return Inertia::render('Admin/AccessLevels/Index', [
            'accessLevels' => $accessLevels,
            'groups' => Group::orderBy('name')->get(),
            'user' => auth()->user(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', AccessLevel::class);

        return Inertia::render('Admin/AccessLevels/Create', [
            'groups' => Group::orderBy('name')->get(),
            'user' => auth()->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', AccessLevel::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'alias' => 'nullable|string|max:255|unique:access_levels',
            'description' => 'nullable|string',
            'groups' => 'array',
            'groups.*' => 'exists:groups,id',
            'status' => 'nullable|boolean',
        ]);

        $alias = $request->alias ? $request->alias : Str::slug($request->title);

        $accessLevel = AccessLevel::create([
            'title' => $request->title,
            'alias' => $alias,
            'description' => $request->description,
            'status' => $request->status ?? true,
            'ordering' => AccessLevel::max('ordering') + 1,
        ]);

        if ($request->has('groups')) {
            $accessLevel->groups()->sync($request->groups);
        }

        return redirect()->route('admin.access-levels.index')
            ->with('success', 'Уровень доступа создан');
    }

    public function edit(int $id): Response
    {
        $accessLevel = AccessLevel::with('groups')->findOrFail($id);
        $this->authorize('update', $accessLevel);

        return Inertia::render('Admin/AccessLevels/Edit', [
            'editAccessLevel' => $accessLevel,
            'groups' => Group::orderBy('name')->get(),
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $accessLevel = AccessLevel::findOrFail($id);
        $this->authorize('update', $accessLevel);

        $request->validate([
            'title' => 'required|string|max:255',
            'alias' => 'nullable|string|max:255|unique:access_levels,alias,'.$id,
            'description' => 'nullable|string',
            'groups' => 'array',
            'groups.*' => 'exists:groups,id',
            'status' => 'nullable|boolean',
        ]);

        $alias = $request->alias ? $request->alias : Str::slug($request->title);

        $accessLevel->update([
            'title' => $request->title,
            'alias' => $alias,
            'description' => $request->description,
            'status' => $request->status ?? true,
        ]);

        if ($request->has('groups')) {
            $accessLevel->groups()->sync($request->groups);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Уровень доступа обновлён']);
        }

        return redirect()->route('admin.access-levels.index')
            ->with('success', 'Уровень доступа обновлён');
    }

    public function destroy(int $id): JsonResponse
    {
        $accessLevel = AccessLevel::findOrFail($id);
        $this->authorize('delete', $accessLevel);

        $accessLevel->groups()->detach();
        $accessLevel->delete();

        return response()->json(['message' => 'Уровень доступа удалён']);
    }

    public function updateOrdering(Request $request): JsonResponse
    {
        $this->authorize('update', AccessLevel::class);

        foreach ($request->order as $item) {
            AccessLevel::where('id', $item['id'])->update(['ordering' => $item['ordering']]);
        }

        return response()->json(['message' => 'Порядок обновлён']);
    }
}
