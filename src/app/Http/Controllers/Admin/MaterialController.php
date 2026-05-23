<?php

namespace App\Http\Controllers\Admin;

use App\DTO\MaterialData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Material\MaterialIndexRequest;
use App\Models\Category;
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
}
