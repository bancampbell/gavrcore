<?php

namespace App\Http\Middleware;

use App\Services\MenuService;
use App\Services\SettingService;
use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     */
    public function share(Request $request): array
    {
        $menuService = app(MenuService::class);
        $settingService = app(SettingService::class);

        $appSettings = $settingService->getAllSettings();
        $siteName = $appSettings['site_name'] ?? 'GavrCore CMS';

        // Тайтл для админки
        $title = $this->getAdminTitle($request, $siteName);

        return array_merge(parent::share($request), [
            'auth.user' => fn () => $request->user()
                ? $request->user()->only('id', 'name', 'email')
                : null,
            'mainMenu' => $menuService->getMenuTree('main-menu'),
            'appSettings' => $appSettings,
            'title' => $title,
        ]);
    }

    /**
     * Get title for admin pages
     */
    protected function getAdminTitle(Request $request, string $siteName): string
    {
        $path = $request->path();

        // Только для админки
        if (!str_starts_with($path, 'admin')) {
            return '';
        }

        $routeName = $request->route()->getName();

        $titles = [
            'materials' => 'Менеджер материалов',
            'categories' => 'Категории',
            'menu' => 'Менеджер меню',
            'users' => 'Пользователи',
            'groups' => 'Группы пользователей',
            'access-levels' => 'Уровни доступа',
            'settings' => 'Общие настройки',
            'media' => 'Медиа-менеджер',
            'dashboard' => 'Панель управления',
        ];

        foreach ($titles as $key => $pageTitle) {
            if (str_contains($routeName, $key)) {
                return $pageTitle;
            }
        }

        return 'Администрирование';
    }
}
