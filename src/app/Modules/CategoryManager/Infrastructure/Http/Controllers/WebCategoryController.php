<?php

namespace App\Modules\CategoryManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;

class WebCategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = CategoryModel::where('alias', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return inertia('Web/Category/Show', [
            'category' => $category,
        ]);
    }
}
