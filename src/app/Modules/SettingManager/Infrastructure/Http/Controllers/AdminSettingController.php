<?php

namespace App\Modules\SettingManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MaterialManager\Application\Services\HomepageManager;
use App\Modules\SettingManager\Application\Services\SettingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminSettingController extends Controller
{
    protected SettingService $settingService;

    protected HomepageManager $homepageManager;

    public function __construct(SettingService $settingService, HomepageManager $homepageManager)
    {
        $this->settingService = $settingService;
        $this->homepageManager = $homepageManager;
    }

    public function index(): Response
    {
        $settings = $this->settingService->getAllSettings();

        return Inertia::render('SettingManager/Settings/Index', [
            'user' => auth()->user(),
            'settings' => $settings,
            'title' => 'Общие настройки',
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string',
            'admin_email' => 'nullable|email',
            'seo_keywords' => 'nullable|string',
            'homepage_type' => 'nullable|string|max:255',
            'homepage_material_id' => 'nullable|integer',
            'materials_per_page' => 'nullable|integer|min:1|max:100',
            'date_format' => 'nullable|string',
            'show_date' => 'nullable|boolean',
            'show_author' => 'nullable|boolean',
            'show_category' => 'nullable|boolean',
            'show_views' => 'nullable|boolean',
            'max_file_size' => 'nullable|integer|min:1|max:50',
            'allowed_formats' => 'nullable|string',
            'image_quality' => 'nullable|integer|min:10|max:100',
            'debug_mode' => 'nullable|boolean',
            'maintenance_mode' => 'nullable|boolean',
            'timezone' => 'nullable|string',
            'robots' => 'nullable|string',
            'google_analytics_id' => 'nullable|string',
            'yandex_metrika_id' => 'nullable|string',
            'cache_enabled' => 'nullable|boolean',
            'cache_ttl' => 'nullable|integer|min:1|max:1440',
        ]);

        $this->settingService->updateSettings($validated);

        // Главная изменена через страницу настроек — приводим флаги материалов
        // к тому же состоянию, чтобы список показывал то, что выбрано здесь
        if (array_key_exists('homepage_type', $validated) || array_key_exists('homepage_material_id', $validated)) {
            $this->homepageManager->syncFlags();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Settings saved successfully']);
        }

        return redirect()->back()->with('success', 'Settings saved');
    }
}
