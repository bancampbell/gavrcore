<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Material\BulkTrashRequest;
use App\Http\Requests\Admin\Material\MaterialIndexRequest;
use App\Http\Requests\Admin\Material\MaterialStoreRequest;
use App\Http\Requests\Admin\Material\UpdateMaterialRequest;
use App\Models\Category;
use App\Models\Material;
use App\Models\User;
use App\Services\MaterialService;
use App\Services\ThemeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Facades\Activity;

class MaterialController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected MaterialService $materialService
    ) {
    }

    public function index(MaterialIndexRequest $request): Response
    {
        $this->authorize('viewAny', Material::class);

        $filters = $request->validated();
        $perPage = $request->input('per_page', 10);
        $materials = $this->materialService->getPaginated($filters, $perPage);

        return Inertia::render('Admin/Materials/Index', [
            'materials' => $materials,
            'categories' => Category::all(),
            'authors' => User::all(),
            'filters' => $filters,
            'perPage' => (int) $perPage,
            'user' => auth()->user(),
            'title' => 'Менеджер материалов',
            'isLanding' => app(ThemeService::class)->isLandingTheme(),
        ]);
    }

    public function bulkTrash(BulkTrashRequest $request): JsonResponse
    {
        $this->authorize('moveToTrash', Material::class);

        $ids = $request->ids;
        $count = count($ids);
        $materials = Material::whereIn('id', $ids)->get();

        foreach ($materials as $material) {
            $material->update(['state' => 'trash']);

            Activity::causedBy(auth()->user())
                ->performedOn($material)
                ->log('Перемещен в корзину: ' . $material->title);
        }

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
            'title' => 'Корзина',
        ]);
    }

    public function restore(BulkTrashRequest $request): JsonResponse
    {
        $this->authorize('restore', Material::class);

        $ids = $request->ids;
        $count = count($ids);
        $materials = Material::whereIn('id', $ids)->get();

        foreach ($materials as $material) {
            $material->update(['state' => 'draft']);

            Activity::causedBy(auth()->user())
                ->performedOn($material)
                ->log('Восстановлен из корзины: ' . $material->title);
        }

        $message = $count === 1 ? 'Материал восстановлен' : 'Материалы восстановлены';

        return response()->json(['message' => $message]);
    }

    public function forceDelete(BulkTrashRequest $request): JsonResponse
    {
        $this->authorize('forceDelete', Material::class);

        $ids = $request->ids;
        $count = count($ids);
        $materials = Material::withTrashed()->whereIn('id', $ids)->get();

        foreach ($materials as $material) {
            $title = $material->title;

            $this->authorize('forceDelete', $material);

            $material->forceDelete();

            Activity::causedBy(auth()->user())
                ->withProperties(['material_id' => $material->id])
                ->log('Полностью удален материал: ' . $title);
        }

        $message = $count === 1 ? 'Материал удалён навсегда' : 'Материалы удалены навсегда';

        return response()->json(['message' => $message]);
    }

    public function emptyTrash(): JsonResponse
    {
        $this->authorize('forceDelete', Material::class);

        $materials = Material::where('state', 'trash')->get();
        $count = $materials->count();

        $titles = $materials->pluck('title')->toArray();

        Material::where('state', 'trash')->forceDelete();

        Activity::causedBy(auth()->user())
            ->withProperties([
                'count' => $count,
                'materials' => $titles,
            ])
            ->log('Очищена корзина материалов: ' . $count . ' материалов');

        return response()->json(['message' => "Корзина очищена, удалено {$count} материалов"]);
    }

    public function bulkPublish(BulkTrashRequest $request): JsonResponse
    {
        $this->authorize('publish', Material::class);

        $ids = $request->ids;
        $count = count($ids);
        $materials = Material::whereIn('id', $ids)->get();

        foreach ($materials as $material) {
            $this->authorize('publish', $material);
            $material->update(['state' => 'published']);
        }

        $titles = $materials->pluck('title')->toArray();
        Activity::causedBy(auth()->user())
            ->withProperties(['materials' => $titles, 'count' => $count])
            ->log('Опубликовано материалов: ' . $count);

        $message = $count === 1 ? 'Материал опубликован' : 'Материалы опубликованы';

        return response()->json(['message' => $message]);
    }

    public function bulkUnpublish(BulkTrashRequest $request): JsonResponse
    {
        $this->authorize('unpublish', Material::class);

        $ids = $request->ids;
        $count = count($ids);
        $materials = Material::whereIn('id', $ids)->get();

        foreach ($materials as $material) {
            $this->authorize('unpublish', $material);
            $material->update(['state' => 'draft']);
        }

        $titles = $materials->pluck('title')->toArray();
        Activity::causedBy(auth()->user())
            ->withProperties(['materials' => $titles, 'count' => $count])
            ->log('Снято с публикации материалов: ' . $count);

        $message = $count === 1 ? 'Материал снят с публикации' : 'Материалы сняты с публикации';

        return response()->json(['message' => $message]);
    }

    public function create(): Response
    {
        $this->authorize('create', Material::class);

        return Inertia::render('Admin/Materials/Create', [
            'categories' => Category::all(),
            'user' => auth()->user(),
            'title' => 'Создать материал',
        ]);
    }

    public function store(MaterialStoreRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Material::class);

        $data = $request->validated();
        $data['user_id'] = auth()->id();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $data['show_date'] = $data['show_date'] ?? true;
        $data['show_author'] = $data['show_author'] ?? true;
        $data['show_category'] = $data['show_category'] ?? true;
        $data['show_views'] = $data['show_views'] ?? true;
        $data['use_global_settings'] = $data['use_global_settings'] ?? true;
        $data['featured'] = $data['featured'] ?? false;
        $data['show_on_homepage'] = $data['show_on_homepage'] ?? false;

        if (!empty($data['show_on_homepage'])) {
            Material::where('show_on_homepage', true)->update(['show_on_homepage' => false]);
        }

        $material = Material::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Материал создан',
                'id' => $material->id,
            ]);
        }

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
            'title' => 'Редактировать материал',
        ]);
    }

    public function update(UpdateMaterialRequest $request, Material $material): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $material);

        $data = $request->validated();

        if (isset($data['title']) && !empty($data['title'])) {
            $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        }

        if (isset($data['show_on_homepage']) && $data['show_on_homepage']) {
            Material::where('id', '!=', $material->id)
                ->where('show_on_homepage', true)
                ->update(['show_on_homepage' => false]);
        }

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
