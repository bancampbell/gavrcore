<?php

use App\Http\Controllers\Admin\AccessLevelController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuTypeController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\PermissionController;
use App\Models\MenuType;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Index');
});

Route::get('/admin/login', fn() => Inertia::render('Auth/Login'))->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/admin/materials', [MaterialController::class, 'index'])->name('admin.materials.index');

    // Корзина и операции с ней
    Route::get('/admin/materials/trash', [MaterialController::class, 'trash'])->name('admin.materials.trash');
    Route::post('/admin/materials/bulk-trash', [MaterialController::class, 'bulkTrash'])->name('admin.materials.bulk-trash');
    Route::post('/admin/materials/restore', [MaterialController::class, 'restore'])->name('admin.materials.restore');
    Route::post('/admin/materials/force-delete', [MaterialController::class, 'forceDelete'])->name('admin.materials.force-delete');
    Route::post('/admin/materials/empty-trash', [MaterialController::class, 'emptyTrash'])->name('admin.materials.empty-trash');
    Route::post('/admin/materials/bulk-publish', [MaterialController::class, 'bulkPublish'])->name('admin.materials.bulk-publish');
    Route::post('/admin/materials/bulk-unpublish', [MaterialController::class, 'bulkUnpublish'])->name('admin.materials.bulk-unpublish');

    Route::resource('/admin/categories', CategoryController::class)->names('admin.categories');

    Route::get('/admin/materials/create', [App\Http\Controllers\Admin\MaterialController::class, 'create'])->name('admin.materials.create');
    Route::post('/admin/materials', [App\Http\Controllers\Admin\MaterialController::class, 'store'])->name('admin.materials.store');
    Route::get('/admin/materials/{material}/edit', [App\Http\Controllers\Admin\MaterialController::class, 'edit'])->name('admin.materials.edit');
    Route::put('/admin/materials/{material}', [App\Http\Controllers\Admin\MaterialController::class, 'update'])->name('admin.materials.update');
    Route::get('/admin/materials/list', [App\Http\Controllers\Admin\MaterialController::class, 'list']);

    Route::prefix('admin/media')->name('admin.media.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\MediaController::class, 'index'])->name('index');
        Route::get('/contents', [App\Http\Controllers\Admin\MediaController::class, 'getContents']);
        Route::post('/folder', [App\Http\Controllers\Admin\MediaController::class, 'createFolder']);
        Route::get('/folders', [App\Http\Controllers\Admin\MediaController::class, 'getFolders']);

        Route::post('/rename', [App\Http\Controllers\Admin\MediaController::class, 'renameItem']);
        Route::delete('/item', [App\Http\Controllers\Admin\MediaController::class, 'deleteItem']);
        Route::post('/copy', [App\Http\Controllers\Admin\MediaController::class, 'copyItem']);
        Route::post('/upload', [App\Http\Controllers\Admin\MediaController::class, 'uploadFile']);
    });

    // Menu Manager Pages (Inertia) - СНАЧАЛА
    Route::get('/admin/menu', [MenuTypeController::class, 'index'])->name('admin.menu.index');

    // Create and Edit pages for menu items
    Route::get('/admin/menu/types/{menuTypeId}/items/create', function ($menuTypeId) {
        $menuType = MenuType::findOrFail($menuTypeId);
        return Inertia::render('Admin/Menu/Create', [
            'user' => auth()->user(),
            'menuTypeId' => $menuTypeId,
        ]);
    })->name('admin.menu.items.create');

    Route::get('/admin/menu/items/{id}/edit', function ($id) {
        $menuItem = App\Models\MenuItem::with('menuType')->findOrFail($id);
        return Inertia::render('Admin/Menu/Edit', [
            'user' => auth()->user(),
            'menuItem' => $menuItem,
            'menuTypeId' => $menuItem->menu_type_id,
        ]);
    })->name('admin.menu.items.edit');

    Route::get('/admin/menu/types/{menuTypeId}/items', function ($menuTypeId) {
        $menuType = MenuType::findOrFail($menuTypeId);
        return Inertia::render('Admin/Menu/MenuItems', [
            'user' => auth()->user(),
            'menuTypeId' => $menuTypeId,
            'menuTypeTitle' => $menuType->title,
        ]);
    })->name('admin.menu.items');

    // Menu Manager API Routes - ПОТОМ
    Route::prefix('admin/menu')->name('admin.menu.')->group(function () {
        // Menu Types
        Route::get('types', [MenuTypeController::class, 'index'])->name('types.index');
        Route::post('types', [MenuTypeController::class, 'store'])->name('types.store');
        Route::get('types/{id}', [MenuTypeController::class, 'show'])->name('types.show');
        Route::put('types/{id}', [MenuTypeController::class, 'update'])->name('types.update');
        Route::delete('types/{id}', [MenuTypeController::class, 'destroy'])->name('types.destroy');
        Route::post('types/ordering/update', [MenuTypeController::class, 'updateOrdering'])->name('types.ordering');

        // Menu Items - ВАЖНО: items/all ДОЛЖЕН БЫТЬ ПЕРВЫМ!
        Route::get('items/all', [MenuItemController::class, 'getAllItems'])->name('items.all');
        Route::get('types/{menuTypeId}/items/tree', [MenuItemController::class, 'tree'])->name('items.tree');
        Route::post('types/{menuTypeId}/items', [MenuItemController::class, 'store'])->name('items.store');
        Route::get('items/{id}', [MenuItemController::class, 'show'])->name('items.show');
        Route::put('items/{id}', [MenuItemController::class, 'update'])->name('items.update');
        Route::delete('items/{id}', [MenuItemController::class, 'destroy'])->name('items.destroy');
        Route::get('types/{menuTypeId}/items', [MenuItemController::class, 'index'])->name('items.index');
        Route::post('items/{id}/status', [MenuItemController::class, 'updateStatus'])->name('items.status');
        Route::post('items/ordering/update', [MenuItemController::class, 'updateOrdering'])->name('items.ordering');
    });

    // User, Group, Permission, AccessLevel Manager
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');

        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::post('/users/bulk-block', [UserController::class, 'bulkBlock'])->name('users.bulk-block');
        Route::post('/users/bulk-unblock', [UserController::class, 'bulkUnblock'])->name('users.bulk-unblock');

        // Groups
        Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
        Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
        Route::get('/groups/{id}/edit', [GroupController::class, 'edit'])->name('groups.edit');

        Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
        Route::put('/groups/{id}', [GroupController::class, 'update'])->name('groups.update');
        Route::delete('/groups/{id}', [GroupController::class, 'destroy'])->name('groups.destroy');
        Route::post('/groups/{id}/status', [GroupController::class, 'updateStatus'])->name('groups.status');

        // Access Levels
        Route::get('/access-levels', [App\Http\Controllers\Admin\AccessLevelController::class, 'index'])->name('access-levels.index');
        Route::get('/access-levels/create', [App\Http\Controllers\Admin\AccessLevelController::class, 'create'])->name('access-levels.create');
        Route::post('/access-levels', [App\Http\Controllers\Admin\AccessLevelController::class, 'store'])->name('access-levels.store');
        Route::get('/access-levels/{id}/edit', [App\Http\Controllers\Admin\AccessLevelController::class, 'edit'])->name('access-levels.edit');
        Route::put('/access-levels/{id}', [App\Http\Controllers\Admin\AccessLevelController::class, 'update'])->name('access-levels.update');
        Route::delete('/access-levels/{id}', [App\Http\Controllers\Admin\AccessLevelController::class, 'destroy'])->name('access-levels.destroy');
        Route::post('/access-levels/ordering', [App\Http\Controllers\Admin\AccessLevelController::class, 'updateOrdering'])->name('access-levels.ordering');
    });
});
