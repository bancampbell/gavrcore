<?php

namespace App\Http\Controllers\Admin;

use App\DTO\MaterialData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Material\BulkTrashRequest;
use App\Http\Requests\Admin\Material\MaterialIndexRequest;
use App\Http\Requests\Admin\Material\StoreMaterialRequest;
use App\Http\Requests\Admin\Material\UpdateMaterialRequest;
use App\Models\Category;
use App\Models\Material;
use App\Models\User;
use App\Services\MaterialService;
use Inertia\Response;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class MaterialController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected MaterialService $materialService
    ) {}

    public function index(MaterialIndexRequest $request): Response
    {
        $this->authorize('viewAny', Material::class);

        $filters = $request->validated();
        $materials = $this->materialService->getPaginated($filters);

        return Inertia::render('Admin/Materials/Index', [
            'materials' => $materials,
            'categories' => Category::all(),
            'authors' => User::all(),
            'filters' => $filters,
            'user' => auth()->user(),
        ]);
    }

    public function bulkTrash(BulkTrashRequest $request): JsonResponse
    {
        $this->authorize('moveToTrash', Material::class);

        $count = count($request->ids);
        Material::whereIn('id', $request->ids)->update(['state' => 'trash']);

        $message = $count === 1 ? 'Материал перемещён в корзину' : 'Материалы перемещены в корзину';

        return response()->json(['message' => $message]);
    }

    public function trash(): Response
    {
        $this->authorize('viewAny', Material::class);

        $materials = Material::with(['category', 'user'])->where('state', 'trash')->paginate(10);

        return Inertia::render('Admin/Materials/Trash', [
            'materials' => $materials,
            'user' => auth()->user(),
        ]);
    }

    public function restore(BulkTrashRequest $request): JsonResponse
    {
        $this->authorize('restore', Material::class);

        $count = count($request->ids);
        Material::whereIn('id', $request->ids)->update(['state' => 'draft']);

        $message = $count === 1 ? 'Материал восстановлен' : 'Материалы восстановлены';
        return response()->json(['message' => $message]);
    }

    public function forceDelete(BulkTrashRequest $request): JsonResponse
    {
        $this->authorize('forceDelete', Material::class);

        $count = count($request->ids);

        foreach ($request->ids as $id) {
            $material = Material::withTrashed()->find($id);
            if ($material) {
                $this->authorize('forceDelete', $material);
            }
        }

        Material::whereIn('id', $request->ids)->forceDelete();

        $message = $count === 1 ? 'Материал удалён навсегда' : 'Материалы удалены навсегда';
        return response()->json(['message' => $message]);
    }

    public function emptyTrash(): JsonResponse
    {
        $this->authorize('forceDelete', Material::class);

        $count = Material::where('state', 'trash')->count();
        Material::where('state', 'trash')->forceDelete();

        return response()->json(['message' => "Корзина очищена, удалено {$count} материалов"]);
    }

    public function bulkPublish(BulkTrashRequest $request): JsonResponse
    {
        $this->authorize('publish', Material::class);

        $count = count($request->ids);

        foreach ($request->ids as $id) {
            $material = Material::find($id);
            if ($material) {
                $this->authorize('publish', $material);
            }
        }

        Material::whereIn('id', $request->ids)->update(['state' => 'published']);

        $message = $count === 1 ? 'Материал опубликован' : 'Материалы опубликованы';
        return response()->json(['message' => $message]);
    }

    public function bulkUnpublish(BulkTrashRequest $request): JsonResponse
    {
        $this->authorize('unpublish', Material::class);

        $count = count($request->ids);

        foreach ($request->ids as $id) {
            $material = Material::find($id);
            if ($material) {
                $this->authorize('unpublish', $material);
            }
        }

        Material::whereIn('id', $request->ids)->update(['state' => 'draft']);

        $message = $count === 1 ? 'Материал снят с публикации' : 'Материалы сняты с публикации';
        return response()->json(['message' => $message]);
    }

    public function create(): Response
    {
        $this->authorize('create', Material::class);

        return Inertia::render('Admin/Materials/Create', [
            'categories' => Category::all(),
            'user' => auth()->user(),
        ]);
    }

    public function store(StoreMaterialRequest $request): RedirectResponse
    {
        $this->authorize('create', Material::class);

        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['alias'] = $data['alias'] ?? \Illuminate\Support\Str::slug($data['title']);

        Material::create($data);

        return redirect()->route('admin.materials.index')
            ->with('success', 'Материал создан');
    }

    public function edit(Material $material): Response
    {
        $this->authorize('update', $material);

        return Inertia::render('Admin/Materials/Edit', [
            'material' => $material,
            'categories' => Category::all(),
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateMaterialRequest $request, Material $material): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $material);

        $data = $request->validated();
        $data['alias'] = $data['alias'] ?? \Illuminate\Support\Str::slug($data['title']);

        $material->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Материал обновлён']);
        }

        return redirect()->route('admin.materials.index')->with('success', 'Материал обновлён');
    }

    public function list(): JsonResponse
    {
        $materials = Material::select('id', 'title', 'category_id', 'slug')->get();
        return response()->json($materials);
    }
}
