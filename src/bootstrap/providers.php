<?php

use App\Providers\AppServiceProvider;
use App\Modules\CategoryManager\Infrastructure\Providers\CategoryManagerServiceProvider;
use App\Modules\FormBuilder\Infrastructure\Providers\FormManagerServiceProvider;
use App\Modules\MenuManager\Infrastructure\Providers\MenuManagerServiceProvider;
use App\Modules\MaterialManager\Infrastructure\Providers\MaterialManagerServiceProvider;
use App\Modules\UserManager\Infrastructure\Providers\UserManagerServiceProvider;
use Modules\MediaManager\Infrastructure\Providers\MediaManagerServiceProvider;
use App\Modules\GalleryManager\Infrastructure\Providers\GalleryManagerServiceProvider;

return [
    AppServiceProvider::class,
    FormManagerServiceProvider::class,
    MenuManagerServiceProvider::class,
    MediaManagerServiceProvider::class,
    MaterialManagerServiceProvider::class,
    GalleryManagerServiceProvider::class,
    CategoryManagerServiceProvider::class,
    UserManagerServiceProvider::class,
];
