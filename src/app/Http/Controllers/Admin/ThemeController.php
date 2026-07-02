<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ThemeController extends Controller
{
    protected ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    public function index(): Response
    {
        $themes = $this->themeService->getAvailableThemes();
        $currentTheme = $this->themeService->getCurrentTheme();

        return Inertia::render('Admin/Themes/Index', [
            'user' => auth()->user(),
            'themes' => $themes,
            'currentTheme' => $currentTheme,
            'title' => 'Темы оформления',
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|string|in:default,dark,warm',
        ]);

        $this->themeService->setCurrentTheme($validated['theme']);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Theme applied successfully']);
        }

        return redirect()->back()->with('success', 'Theme applied successfully');
    }
}
