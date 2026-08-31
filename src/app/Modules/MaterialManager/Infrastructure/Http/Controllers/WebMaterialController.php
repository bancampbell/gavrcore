<?php

namespace App\Modules\MaterialManager\Infrastructure\Http\Controllers;

use App\Modules\MaterialManager\Application\DTO\MaterialFiltersData;
use App\Modules\MaterialManager\Application\UseCases\GetMaterialsUseCase;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\CategoryServiceInterface;
use App\Modules\MaterialManager\Domain\Services\ContentParserInterface;
use App\Modules\MaterialManager\Domain\Services\FormServiceInterface;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialAccess;
use App\Modules\MaterialManager\Infrastructure\Http\Resources\MaterialResource;
use App\Modules\MenuManager\Application\UseCases\GetMenuTreeUseCase;
use App\Services\BreadcrumbService;
use App\Services\SettingService;
use App\Services\ThemeService;
use App\Seo\Services\MetaService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebMaterialController
{
    public function __construct(
        private readonly GetMaterialsUseCase $getMaterials,
        private readonly MaterialRepositoryInterface $repository,
        private readonly SettingService $settingService,
        private readonly GetMenuTreeUseCase $getMenuTree,
        private readonly ThemeService $themeService,
        private readonly BreadcrumbService $breadcrumbService,
        private readonly MetaService $metaService,
        private readonly ContentParserInterface $contentParser,
        private readonly CategoryServiceInterface $categoryService,
        private readonly FormServiceInterface $formService,
    ) {}

    public function index(): Response
    {
        $settings = $this->settingService->getAllSettings();
        $siteName = $settings['site_name'] ?? 'GavrCore CMS';
        $siteDescription = $settings['site_description'] ?? '';
        $siteKeywords = $settings['seo_keywords'] ?? '';
        $currentTheme = $this->settingService->getTheme();

        if ($this->themeService->isLandingTheme()) {
            $allForms = $this->formService->findAllActive();

            return Inertia::render('landing/Index', [
                'appSettings' => $settings,
                'currentTheme' => $currentTheme,
                'mainMenu' => $this->getMenuTree->execute('main-menu'),
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
                'breadcrumbs' => $this->breadcrumbService->forHome(),
            ]);
        }

        $homepageMaterial = $this->repository->findHomepage();

        if ($homepageMaterial !== null && $homepageMaterial->access !== MaterialAccess::PUBLIC) {
            $user = auth()->user();
            $allowed = false;

            if ($user !== null) {
                if ($homepageMaterial->access === MaterialAccess::REGISTERED) {
                    $allowed = true;
                } elseif ($homepageMaterial->access === MaterialAccess::SPECIAL && $user->hasPermission('materials.special')) {
                    $allowed = true;
                }
            }

            if (!$allowed) {
                $homepageMaterial = null;
            }
        }

        $forms = [];
        if ($homepageMaterial && $homepageMaterial->content) {
            $formIds = $this->contentParser->extractFormIds($homepageMaterial->content);
            if (!empty($formIds)) {
                $forms = $this->formService->findActiveByIds($formIds);
            }
        }

        if ($homepageMaterial) {
            $meta = $this->metaService->for($homepageMaterial);
            $metaArray = $meta->toArray();
            $breadcrumbs = $this->breadcrumbService->forHome();
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
            $breadcrumbs = $this->breadcrumbService->forHome();
        }

        return Inertia::render('Index', [
            'homepageMaterial' => $homepageMaterial ? (new MaterialResource($homepageMaterial))->toArray(request()) : null,
            'forms' => $forms,
            'mainMenu' => $this->getMenuTree->execute('main-menu'),
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
        $material = $this->repository->findBySlug($slug);

        if (!$material) {
            abort(404);
        }

        if ($material->access !== MaterialAccess::PUBLIC) {
            if (!auth()->check()) {
                abort(403);
            }

            if ($material->access === MaterialAccess::SPECIAL && !auth()->user()->hasPermission('materials.special')) {
                abort(403);
            }
        }

        $this->repository->incrementViews($material);

        $formIds = $this->contentParser->extractFormIds($material->content);
        $forms = [];

        if (!empty($formIds)) {
            $forms = $this->formService->findActiveByIds($formIds);
        }

        $settings = $this->settingService->getAllSettings();
        $currentTheme = $this->settingService->getTheme();

        $meta = $this->metaService->for($material);
        $breadcrumbs = $this->breadcrumbService->forMaterial($material);

        return Inertia::render('MaterialManager/Show', [
            'material' => (new MaterialResource($material))->toArray(request()),
            'template' => $material->template ?? 'default',
            'forms' => $forms,
            'mainMenu' => $this->getMenuTree->execute('main-menu'),
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

        $filters = MaterialFiltersData::fromRequest([
            'search' => $request->get('search'),
            'category_id' => $category->id,
            'state' => 'published',
            'per_page' => 10,
            'access_levels' => $this->allowedAccessLevels(),
        ]);

        $materials = $this->getMaterials->execute($filters);

        $settings = $this->settingService->getAllSettings();
        $currentTheme = $this->settingService->getTheme();

        $meta = $this->metaService->for($category);
        $breadcrumbs = $this->breadcrumbService->forCategory($category);

        return Inertia::render('Category/Show', [
            'category' => $category,
            'materials' => $materials->toArray(),
            'categories' => $this->categoryService->getAll(),
            'filters' => $filters->toArray(),
            'mainMenu' => $this->getMenuTree->execute('main-menu'),
            'meta' => $meta->toArray(),
            'appSettings' => $settings,
            'currentTheme' => $currentTheme,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function search(Request $request): Response
    {
        $search = $request->get('q');

        $filters = MaterialFiltersData::fromRequest([
            'search' => $search,
            'state' => 'published',
            'per_page' => 10,
            'access_levels' => $this->allowedAccessLevels(),
        ]);

        $materials = $this->getMaterials->execute($filters);

        $title = $search ? "Поиск: $search" : 'Поиск';
        $settings = $this->settingService->getAllSettings();
        $siteDescription = $settings['site_description'] ?? '';
        $siteKeywords = $settings['seo_keywords'] ?? '';
        $currentTheme = $this->settingService->getTheme();

        $breadcrumbs = $this->breadcrumbService->forSearch($search);

        return Inertia::render('Search/Index', [
            'materials' => $materials->toArray(),
            'categories' => $this->categoryService->getAll(),
            'search' => $search,
            'mainMenu' => $this->getMenuTree->execute('main-menu'),
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

    private function allowedAccessLevels(): array
    {
        $levels = [MaterialAccess::PUBLIC->value];

        if (auth()->check()) {
            $levels[] = MaterialAccess::REGISTERED->value;

            if (auth()->user()->hasPermission('materials.special')) {
                $levels[] = MaterialAccess::SPECIAL->value;
            }
        }

        return $levels;
    }

    private function getHomepageSlug(): ?string
    {
        $settings = $this->settingService->getAllSettings();
        $homepageId = (int) ($settings['homepage_material_id'] ?? 0);

        if ($homepageId === 0) {
            return null;
        }

        $homepage = $this->repository->findById($homepageId);

        return $homepage?->slug;
    }
}
