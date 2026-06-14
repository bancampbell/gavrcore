<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Material;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalMaterials = Material::count();
        $totalCategories = Category::count();
        $totalUsers = User::count();
        $totalViews = Material::sum('views');

        return Inertia::render('Admin/Dashboard', [
            'user' => auth()->user(),
            'stats' => [
                'materials' => $totalMaterials,
                'categories' => $totalCategories,
                'users' => $totalUsers,
                'views' => $totalViews,
            ],
            'title' => 'Панель управления',
        ]);
    }
}
