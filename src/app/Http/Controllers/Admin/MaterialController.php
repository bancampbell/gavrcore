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
use Inertia\Inertia;

class MaterialController extends Controller
{
    public function __construct(
        protected MaterialService $materialService
    ) {}

    public function index(MaterialIndexRequest $request)
    {
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

    public function bulkTrash(BulkTrashRequest $request)
    {
        $count = count($request->ids);
        Material::whereIn('id', $request->ids)->update(['state' => 'trash']);

        $message = $count === 1 ? 'Материал перемещён в корзину' : 'Материалы перемещены в корзину';

        return response()->json(['message' => $message]);
    }

    public function trash()
    {
        $materials = Material::with(['category', 'user'])->where('state', 'trash')->paginate(10);

        return Inertia::render('Admin/Materials/Trash', [
            'materials' => $materials,
            'user' => auth()->user(),
        ]);
    }

    public function restore(BulkTrashRequest $request)
    {
        $count = count($request->ids);
        Material::whereIn('id', $request->ids)->update(['state' => 'draft']);

        $message = $count === 1 ? 'Материал восстановлен' : 'Материалы восстановлены';
        return response()->json(['message' => $message]);
    }

    public function forceDelete(BulkTrashRequest $request)
    {
        $count = count($request->ids);
        Material::whereIn('id', $request->ids)->forceDelete();

        $message = $count === 1 ? 'Материал удалён навсегда' : 'Материалы удалены навсегда';
        return response()->json(['message' => $message]);
    }

    public function emptyTrash()
    {
        $count = Material::where('state', 'trash')->count();
        Material::where('state', 'trash')->forceDelete();

        return response()->json(['message' => "Корзина очищена, удалено {$count} материалов"]);
    }

    public function bulkPublish(BulkTrashRequest $request)
    {
        $count = count($request->ids);
        Material::whereIn('id', $request->ids)->update(['state' => 'published']);

        $message = $count === 1 ? 'Материал опубликован' : 'Материалы опубликованы';
        return response()->json(['message' => $message]);
    }

    public function bulkUnpublish(BulkTrashRequest $request)
    {
        $count = count($request->ids);
        Material::whereIn('id', $request->ids)->update(['state' => 'draft']);

        $message = $count === 1 ? 'Материал снят с публикации' : 'Материалы сняты с публикации';
        return response()->json(['message' => $message]);
    }


    public function create()
    {
        return Inertia::render('Admin/Materials/Create', [
            'categories' => Category::all(),
            'user' => auth()->user(),
        ]);
    }

    public function store(StoreMaterialRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['alias'] = $data['alias'] ?? \Illuminate\Support\Str::slug($data['title']);

        Material::create($data);

        return redirect()->route('admin.materials.index')
            ->with('success', 'Материал создан');
    }

    public function edit(Material $material)
    {
        return Inertia::render('Admin/Materials/Edit', [
            'material' => $material,
            'categories' => Category::all(),
            'user' => auth()->user(),
        ]);
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        $data = $request->validated();
        $data['alias'] = $data['alias'] ?? \Illuminate\Support\Str::slug($data['title']);

        $material->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Материал обновлён']);
        }

        return redirect()->route('admin.materials.index')->with('success', 'Материал обновлён');
    }

    public function list()
    {
        $materials = Material::select('id', 'title', 'category_id', 'slug')->get();
        return response()->json($materials);
    }


}
