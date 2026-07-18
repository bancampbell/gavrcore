<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Cache;

trait ClearsSitemapCache
{
    protected static function bootClearsSitemapCache()
    {
        static::saved(function () {
            Cache::forget('sitemap');
        });

        static::deleted(function () {
            Cache::forget('sitemap');
        });
    }
}
