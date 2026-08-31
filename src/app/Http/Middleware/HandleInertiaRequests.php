<?php

namespace App\Http\Middleware;

use App\Modules\MenuManager\Application\UseCases\GetMenuTreeUseCase;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $menuUseCase = app(GetMenuTreeUseCase::class);
        $settingService = app(SettingService::class);

        $appSettings = $settingService->getAllSettings();
        $siteName = $appSettings['site_name'] ?? 'GavrCore CMS';

        $title = $this->getAdminTitle($request, $siteName);

        return array_merge(parent::share($request), [
            'auth.user' => fn () => $request->user()
                ? $request->user()->only('id', 'name', 'email')
                : null,
            'mainMenu' => $menuUseCase->execute('main-menu'),
            'appSettings' => $appSettings,
            'title' => $title,
            'currentTheme' => $settingService->getTheme(),
            'flash' => fn () => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }

    protected function getAdminTitle(Request $request, string $siteName): string
    {
        $path = $request->path();

        if (!str_starts_with($path, 'admin')) {
            return '';
        }

        $routeName = $request->route()?->getName() ?? '';

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
