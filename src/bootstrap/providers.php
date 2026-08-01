<?php

use App\Providers\AppServiceProvider;
use Modules\MediaManager\Infrastructure\Providers\MediaManagerServiceProvider;

return [
    AppServiceProvider::class,
    MediaManagerServiceProvider::class,
];
