<?php

use App\Http\Controllers\Admin\MaterialController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Index');
});

Route::get('/admin/login', fn() => Inertia::render('Auth/Login'))->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard', [
            'user' => auth()->user(),
        ]);
    })->name('dashboard');

    Route::get('/admin/materials', [MaterialController::class, 'index'])->name('admin.materials.index');
});
