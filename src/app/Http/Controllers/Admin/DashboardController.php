<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Category;
use App\Models\User;
use Inertia\Response;
use Inertia\Inertia;

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
        ]);
    }
}
