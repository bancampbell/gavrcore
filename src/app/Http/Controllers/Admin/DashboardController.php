<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use App\Models\User;
use App\Modules\MaterialManager\Infrastructure\Models\MaterialModel as Material;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalMaterials = Material::count();
        $totalCategories = CategoryModel::count();
        $totalUsers = User::count();
        $totalViews = Material::sum('views');

        $activities = Activity::with('causer', 'subject')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Dashboard', [
            'user' => auth()->user(),
            'stats' => [
                'materials' => $totalMaterials,
                'categories' => $totalCategories,
                'users' => $totalUsers,
                'views' => $totalViews,
            ],
            'activities' => $activities,
            'title' => 'Панель управления',
        ]);
    }
}
