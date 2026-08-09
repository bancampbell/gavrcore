<?php

use App\Providers\AppServiceProvider;
use App\Modules\MaterialManager\Infrastructure\Providers\MaterialManagerServiceProvider;
use Modules\MediaManager\Infrastructure\Providers\MediaManagerServiceProvider;

return [
    AppServiceProvider::class,
    MediaManagerServiceProvider::class,
    MaterialManagerServiceProvider::class,
];
