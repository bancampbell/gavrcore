<?php

use App\Providers\AppServiceProvider;
use App\Modules\MaterialManager\Infrastructure\Providers\MaterialManagerServiceProvider;
use Modules\MediaManager\Infrastructure\Providers\MediaManagerServiceProvider;
use App\Modules\GalleryManager\Infrastructure\Providers\GalleryManagerServiceProvider;

return [
    AppServiceProvider::class,
    MediaManagerServiceProvider::class,
    MaterialManagerServiceProvider::class,
    GalleryManagerServiceProvider::class,
];
