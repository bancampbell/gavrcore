<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\MaterialService;
use App\Services\CategoryService;
use App\Services\MenuService;
use App\Models\Material;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaterialController extends Controller
{
    public function __construct(
        protected MaterialService $materialService,
        protected CategoryService $categoryService,
        protected MenuService $menuService
    ) {
    }

    public function index(): Response
    {
        $homepageMaterial = Material::where('show_on_homepage', true)
            ->where('state', 'published')
            ->with(['category', 'user'])
            ->first();

        return Inertia::render('Index', [
            'homepageMaterial' => $homepageMaterial,
            'mainMenu' => $this->menuService->getMenuTree('main-menu'),
        ]);
    }

    public function show(string $slug): Response
    {
        $material = Material::where('alias', $slug)
            ->where('state', 'published')
            ->with(['category', 'user'])
            ->firstOrFail();

        $material->increment('views');

        return Inertia::render('Material/Show', [
            'material' => $material,
            'mainMenu' => $this->menuService->getMenuTree('main-menu'),
        ]);
    }

    public function category(string $slug, Request $request): Response
    {
        $category = $this->categoryService->findBySlug($slug);

        if (!$category) {
            abort(404);
        }

        $filters = $request->only(['search']);
        $filters['category_id'] = $category->id;
        $filters['state'] = 'published';

        $materials = $this->materialService->getPaginated($filters);
        $categories = $this->categoryService->getAllForSelect();

        return Inertia::render('Category/Show', [
            'category' => $category,
            'materials' => $materials,
            'categories' => $categories,
            'filters' => $filters,
            'mainMenu' => $this->menuService->getMenuTree('main-menu'),
        ]);
    }

    public function search(Request $request): Response
    {
        $search = $request->get('q');

        $filters = [
            'search' => $search,
            'state' => 'published',
        ];

        $materials = $this->materialService->getPaginated($filters);
        $categories = $this->categoryService->getAllForSelect();

        return Inertia::render('Search/Index', [
            'materials' => $materials,
            'categories' => $categories,
            'search' => $search,
            'mainMenu' => $this->menuService->getMenuTree('main-menu'),
        ]);
    }
}
