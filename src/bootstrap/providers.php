<?php

use App\Modules\AdminDashboard\Infrastructure\Providers\AdminDashboardServiceProvider;
use App\Modules\CategoryManager\Infrastructure\Providers\CategoryManagerServiceProvider;
use App\Modules\CookieConsent\Infrastructure\Providers\CookieConsentServiceProvider;
use App\Modules\FormBuilder\Infrastructure\Providers\FormManagerServiceProvider;
use App\Modules\GalleryManager\Infrastructure\Providers\GalleryManagerServiceProvider;
use App\Modules\MaterialManager\Infrastructure\Providers\MaterialManagerServiceProvider;
use App\Modules\MenuManager\Infrastructure\Providers\MenuManagerServiceProvider;
use App\Modules\SeoManager\Infrastructure\Providers\SeoManagerServiceProvider;
use App\Modules\SettingManager\Infrastructure\Providers\SettingManagerServiceProvider;
use App\Modules\ThemeManager\Infrastructure\Providers\ThemeManagerServiceProvider;
use App\Modules\UserDashboard\Infrastructure\Providers\UserDashboardServiceProvider;
use App\Modules\UserManager\Infrastructure\Providers\UserManagerServiceProvider;
use App\Providers\AppServiceProvider;
use Modules\MediaManager\Infrastructure\Providers\MediaManagerServiceProvider;

return [
    AppServiceProvider::class,
    FormManagerServiceProvider::class,
    MenuManagerServiceProvider::class,
    MediaManagerServiceProvider::class,
    MaterialManagerServiceProvider::class,
    GalleryManagerServiceProvider::class,
    CategoryManagerServiceProvider::class,
    UserManagerServiceProvider::class,
    SettingManagerServiceProvider::class,
    ThemeManagerServiceProvider::class,
    SeoManagerServiceProvider::class,
    UserDashboardServiceProvider::class,
    AdminDashboardServiceProvider::class,
    CookieConsentServiceProvider::class,
];
