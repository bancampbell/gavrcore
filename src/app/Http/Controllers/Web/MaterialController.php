<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\MaterialService;
use App\Services\CategoryService;
use App\Services\MenuService;
use App\Services\SettingService;
use App\Services\BreadcrumbService;
use App\Services\ThemeService;
use App\Models\Material;
use App\Models\Form;
use App\Seo\Services\MetaService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaterialController extends Controller
{
    protected SettingService $settingService;

    public function __construct(
        protected MaterialService $materialService,
        protected CategoryService $categoryService,
        protected MenuService $menuService
    ) {
        $this->settingService = app(SettingService::class);
    }

    public function index(): Response
    {
        $settings = $this->settingService->getAllSettings();
        $siteName = $settings['site_name'] ?? 'GavrCore CMS';
        $siteDescription = $settings['site_description'] ?? '';
        $siteKeywords = $settings['seo_keywords'] ?? '';
        $currentTheme = $this->settingService->getTheme();

        // Проверяем, выбрана ли тема лендинга
        $themeService = app(ThemeService::class);
        if ($themeService->isLandingTheme()) {
            $allForms = Form::where('status', true)->get()->keyBy('id')->toArray();

            return Inertia::render('landing/Index', [
                'appSettings' => $settings,
                'currentTheme' => $currentTheme,
                'mainMenu' => $this->menuService->getMenuTree('main-menu'),
                'title' => $siteName,
                'description' => $siteDescription,
                'keywords' => $siteKeywords,
                'forms' => $allForms,
                'meta' => [
                    'title' => $siteName,
                    'description' => $siteDescription,
                    'keywords' => $siteKeywords,
                    'og_title' => $siteName,
                    'og_description' => $siteDescription,
                    'og_type' => 'website',
                    'twitter_card' => 'summary_large_image',
                    'canonical' => url('/'),
                ],
                'breadcrumbs' => app(BreadcrumbService::class)->forHome(),
            ]);
        }

        $homepageMaterial = Material::where('show_on_homepage', true)
            ->where('state', 'published')
            ->with(['category', 'user'])
            ->first();

        $forms = [];
        if ($homepageMaterial && $homepageMaterial->content) {
            $formIds = $this->extractFormIds($homepageMaterial->content);
            if (!empty($formIds)) {
                $forms = Form::whereIn('id', $formIds)
                    ->where('status', true)
                    ->get()
                    ->keyBy('id')
                    ->toArray();
            }
        }

        if ($homepageMaterial) {
            $meta = app(MetaService::class)->for($homepageMaterial);
            $metaArray = $meta->toArray();
            $breadcrumbs = app(BreadcrumbService::class)->forHome();
        } else {
            $metaArray = [
                'title' => $siteName,
                'description' => $siteDescription,
                'keywords' => $siteKeywords,
                'og_title' => $siteName,
                'og_description' => $siteDescription,
                'og_type' => 'website',
                'twitter_card' => 'summary_large_image',
                'canonical' => url('/'),
            ];
            $breadcrumbs = app(BreadcrumbService::class)->forHome();
        }

        return Inertia::render('Index', [
            'homepageMaterial' => $homepageMaterial,
            'forms' => $forms,
            'mainMenu' => $this->menuService->getMenuTree('main-menu'),
            'title' => $siteName,
            'description' => $siteDescription,
            'keywords' => $siteKeywords,
            'appSettings' => $settings,
            'currentTheme' => $currentTheme,
            'meta' => $metaArray,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function show(string $slug): Response
    {
        $material = Material::where('slug', $slug)
            ->where('state', 'published')
            ->with(['category', 'user'])
            ->firstOrFail();

        $material->increment('views');

        $formIds = $this->extractFormIds($material->content);
        $forms = [];

        if (!empty($formIds)) {
            $forms = Form::whereIn('id', $formIds)
                ->where('status', true)
                ->get()
                ->keyBy('id')
                ->toArray();
        }

        $settings = $this->settingService->getAllSettings();
        $currentTheme = $this->settingService->getTheme();

        $meta = app(MetaService::class)->for($material);
        $breadcrumbs = app(BreadcrumbService::class)->forMaterial($material);

        return Inertia::render('Material/Show', [
            'material' => $material,
            'template' => $material->template ?? 'default',
            'forms' => $forms,
            'mainMenu' => $this->menuService->getMenuTree('main-menu'),
            'meta' => $meta->toArray(),
            'appSettings' => $settings,
            'currentTheme' => $currentTheme,
            'breadcrumbs' => $breadcrumbs,
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

        $settings = $this->settingService->getAllSettings();
        $currentTheme = $this->settingService->getTheme();

        $meta = app(MetaService::class)->for($category);
        $breadcrumbs = app(BreadcrumbService::class)->forCategory($category);

        return Inertia::render('Category/Show', [
            'category' => $category,
            'materials' => $materials,
            'categories' => $categories,
            'filters' => $filters,
            'mainMenu' => $this->menuService->getMenuTree('main-menu'),
            'meta' => $meta->toArray(),
            'appSettings' => $settings,
            'currentTheme' => $currentTheme,
            'breadcrumbs' => $breadcrumbs,
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

        $title = $search ? "Поиск: $search" : 'Поиск';
        $settings = $this->settingService->getAllSettings();
        $siteDescription = $settings['site_description'] ?? '';
        $siteKeywords = $settings['seo_keywords'] ?? '';
        $currentTheme = $this->settingService->getTheme();

        $breadcrumbs = app(BreadcrumbService::class)->forSearch($search);

        return Inertia::render('Search/Index', [
            'materials' => $materials,
            'categories' => $categories,
            'search' => $search,
            'mainMenu' => $this->menuService->getMenuTree('main-menu'),
            'title' => $title,
            'description' => $siteDescription,
            'keywords' => $siteKeywords,
            'appSettings' => $settings,
            'currentTheme' => $currentTheme,
            'meta' => [
                'title' => $title,
                'description' => $siteDescription,
                'keywords' => $siteKeywords,
                'og_title' => $title,
                'og_description' => $siteDescription,
                'og_type' => 'website',
                'twitter_card' => 'summary_large_image',
                'canonical' => url()->current(),
            ],
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    private function extractFormIds(?string $content): array
    {
        if (!$content) {
            return [];
        }

        preg_match_all('/\[form id="(\d+)"\]/', $content, $matches);
        return array_map('intval', $matches[1] ?? []);
    }
}
