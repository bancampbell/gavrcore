<?php

use App\Http\Controllers\Admin\AccessLevelController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\FormSubmissionController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\MenuTypeController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Auth\User\LoginController as UserLoginController;
use App\Http\Controllers\Auth\User\RegisterController;
use App\Http\Controllers\Web\CookieConsentController;
use App\Http\Controllers\Web\MaterialController as WebMaterialController;
use App\Http\Controllers\Web\SitemapController;
use App\Http\Controllers\Web\DashboardController as WebDashboardController;
use App\Models\MenuItem;
use App\Models\MenuType;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ===== COOKIE CONSENT =====
Route::post('/cookie-consent/accept', [CookieConsentController::class, 'accept'])->name('cookie.consent.accept');
Route::post('/cookie-consent/decline', [CookieConsentController::class, 'decline'])->name('cookie.consent.decline');

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// ===== ПОЛЬЗОВАТЕЛЬСКАЯ АУТЕНТИФИКАЦИЯ =====
Route::get('/login', [UserLoginController::class, 'create'])->name('login');
Route::post('/login', [UserLoginController::class, 'store']);
Route::post('/logout', [UserLoginController::class, 'logout'])->name('logout');

// ===== РЕГИСТРАЦИЯ =====
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

// ===== ЛИЧНЫЙ КАБИНЕТ ПОЛЬЗОВАТЕЛЯ =====
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [WebDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/profile', [WebDashboardController::class, 'profile'])->name('dashboard.profile');
    Route::get('/dashboard/settings', [WebDashboardController::class, 'settings'])->name('dashboard.settings');

    Route::put('/dashboard/profile', [WebDashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
    Route::put('/dashboard/settings', [WebDashboardController::class, 'updateSettings'])->name('dashboard.settings.update');
});

// Публичные роуты
Route::get('/', [WebMaterialController::class, 'index'])->name('home');
Route::get('/category/{slug}', [WebMaterialController::class, 'category'])->name('category.show');
Route::get('/search', [WebMaterialController::class, 'search'])->name('search');

// Материалы с красивыми URL (без /material/)
Route::get('/{slug}', [WebMaterialController::class, 'show'])->name('page.show')->where('slug', '^(?!admin|category|search|login|register|sitemap|cookie-consent|dashboard).+');

// ===== АДМИНСКАЯ АУТЕНТИФИКАЦИЯ =====
Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->middleware('throttle:login');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// ===== ЗАЩИЩЁННЫЕ АДМИНСКИЕ РОУТЫ =====
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gallery Manager
    Route::get('/admin/galleries', [GalleryController::class, 'index'])->name('admin.galleries.index');
    Route::get('/admin/galleries/list', [GalleryController::class, 'list'])->name('admin.galleries.list');
    Route::get('/admin/galleries/create', [GalleryController::class, 'create'])->name('admin.galleries.create');
    Route::post('/admin/galleries', [GalleryController::class, 'store'])->name('admin.galleries.store');
    Route::get('/admin/galleries/{gallery}/edit', [GalleryController::class, 'edit'])->name('admin.galleries.edit');
    Route::put('/admin/galleries/{gallery}', [GalleryController::class, 'update'])->name('admin.galleries.update');
    Route::delete('/admin/galleries/{gallery}', [GalleryController::class, 'destroy'])->name('admin.galleries.destroy');

    // Gallery Images
    Route::post('/admin/galleries/{gallery}/images', [GalleryController::class, 'uploadImage'])->name('admin.galleries.images.upload');
    Route::put('/admin/galleries/{gallery}/images/{image}', [GalleryController::class, 'updateImage'])->name('admin.galleries.images.update');
    Route::delete('/admin/galleries/{gallery}/images/{image}', [GalleryController::class, 'deleteImage'])->name('admin.galleries.images.delete');

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

    Route::get('/admin/materials/create', [MaterialController::class, 'create'])->name('admin.materials.create');
    Route::post('/admin/materials', [MaterialController::class, 'store'])->name('admin.materials.store');
    Route::get('/admin/materials/{material}/edit', [MaterialController::class, 'edit'])->name('admin.materials.edit');
    Route::put('/admin/materials/{material}', [MaterialController::class, 'update'])->name('admin.materials.update');
    Route::get('/admin/materials/list', [MaterialController::class, 'list']);

    Route::prefix('admin/media')->name('admin.media.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::get('/contents', [MediaController::class, 'getContents']);
        Route::post('/folder', [MediaController::class, 'createFolder']);
        Route::get('/folders', [MediaController::class, 'getFolders']);

        Route::post('/rename', [MediaController::class, 'renameItem']);
        Route::delete('/item', [MediaController::class, 'deleteItem']);
        Route::delete('/items', [MediaController::class, 'deleteMultiple']);
        Route::post('/copy', [MediaController::class, 'copyItem']);
        Route::post('/upload', [MediaController::class, 'uploadFile']);
    });

    // Menu Manager Pages (Inertia)
    Route::get('/admin/menu', [MenuTypeController::class, 'index'])->name('admin.menu.index');

    // Create page for menu items
    Route::get('/admin/menu/types/{menuTypeId}/items/create', function ($menuTypeId) {
        $menuType = MenuType::findOrFail($menuTypeId);
        return Inertia::render('Admin/Menu/Create', [
            'user' => auth()->user(),
            'menuTypeId' => $menuTypeId,
            'title' => "Создать пункт меню: {$menuType->title}",
        ]);
    })->name('admin.menu.items.create');

    // Edit page for menu items
    Route::get('/admin/menu/items/{id}/edit', function ($id) {
        $menuItem = MenuItem::with('menuType')->findOrFail($id);
        return Inertia::render('Admin/Menu/Edit', [
            'user' => auth()->user(),
            'menuItem' => $menuItem,
            'menuTypeId' => $menuItem->menu_type_id,
            'title' => "Редактировать пункт меню: {$menuItem->title}",
        ]);
    })->name('admin.menu.items.edit');

    // Menu items list page
    Route::get('/admin/menu/types/{menuTypeId}/items', function ($menuTypeId) {
        $menuType = MenuType::findOrFail($menuTypeId);

        return Inertia::render('Admin/Menu/MenuItems', [
            'user' => auth()->user(),
            'menuTypeId' => $menuTypeId,
            'menuTypeTitle' => $menuType->title,
        ]);
    })->name('admin.menu.items');

    // Menu Manager API Routes
    Route::prefix('admin/menu')->name('admin.menu.')->group(function () {
        // Menu Types
        Route::get('types', [MenuTypeController::class, 'index'])->name('types.index');
        Route::post('types', [MenuTypeController::class, 'store'])->name('types.store');
        Route::get('types/{id}', [MenuTypeController::class, 'show'])->name('types.show');
        Route::put('types/{id}', [MenuTypeController::class, 'update'])->name('types.update');
        Route::delete('types/{id}', [MenuTypeController::class, 'destroy'])->name('types.destroy');
        Route::post('types/{id}/status', [MenuTypeController::class, 'updateStatus'])->name('types.status');
        Route::post('types/ordering/update', [MenuTypeController::class, 'updateOrdering'])->name('types.ordering');

        // Menu Items
        Route::get('items/all', [MenuItemController::class, 'getAllItemsPage'])->name('items.all');
        Route::get('items/all-data', [MenuItemController::class, 'getAllItems'])->name('items.all-data');
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
        Route::get('/access-levels', [AccessLevelController::class, 'index'])->name('access-levels.index');
        Route::get('/access-levels/create', [AccessLevelController::class, 'create'])->name('access-levels.create');
        Route::post('/access-levels', [AccessLevelController::class, 'store'])->name('access-levels.store');
        Route::get('/access-levels/{id}/edit', [AccessLevelController::class, 'edit'])->name('access-levels.edit');
        Route::put('/access-levels/{id}', [AccessLevelController::class, 'update'])->name('access-levels.update');
        Route::delete('/access-levels/{id}', [AccessLevelController::class, 'destroy'])->name('access-levels.destroy');
        Route::post('/access-levels/ordering', [AccessLevelController::class, 'updateOrdering'])->name('access-levels.ordering');
        Route::patch('/access-levels/{id}/status', [AccessLevelController::class, 'updateStatus'])->name('access-levels.status');
    });

    // Settings
    Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');

    // Themes
    Route::get('/admin/themes', [ThemeController::class, 'index'])->name('admin.themes.index');
    Route::post('/admin/themes', [ThemeController::class, 'update'])->name('admin.themes.update');

    // ========================================
    // FORMS
    // ========================================
    Route::prefix('admin/forms')->name('admin.forms.')->group(function () {
        Route::get('/', [FormController::class, 'index'])->name('index');
        Route::get('/create', [FormController::class, 'create'])->name('create');
        Route::post('/', [FormController::class, 'store'])->name('store');
        Route::get('/{form}/edit', [FormController::class, 'edit'])->name('edit');
        Route::put('/{form}', [FormController::class, 'update'])->name('update');
        Route::put('/{form}/status', [FormController::class, 'updateStatus'])->name('status');
        Route::delete('/{form}', [FormController::class, 'destroy'])->name('destroy');
        Route::get('/list', [FormController::class, 'list'])->name('list');

        Route::get('/{form}/builder', [FormController::class, 'builder'])->name('builder');
        Route::put('/{form}/fields', [FormController::class, 'updateFields'])->name('fields.update');
    });

    // ========================================
    // FORM SUBMISSIONS (Обратная связь)
    // ========================================
    Route::prefix('admin/submissions')->name('admin.submissions.')->group(function () {
        Route::get('/', [FormSubmissionController::class, 'index'])->name('index');
        Route::get('/{id}', [FormSubmissionController::class, 'show'])->name('show');
        Route::delete('/{id}', [FormSubmissionController::class, 'destroy'])->name('destroy');
        Route::post('/mark-read', [FormSubmissionController::class, 'markAsReadBulk'])->name('mark-read');
        Route::post('/destroy-bulk', [FormSubmissionController::class, 'destroyBulk'])->name('destroy-bulk');
    });
});
