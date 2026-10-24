<?php

namespace App\Modules\SeoManager\Infrastructure\Listeners;

use App\Modules\CategoryManager\Domain\Events\CategorySaved;
use App\Modules\MaterialManager\Domain\Events\MaterialSaved;
use Illuminate\Support\Facades\Cache;

class ClearSitemapCacheListener
{
    public function handleMaterialSaved(MaterialSaved $event): void
    {
        Cache::forget('sitemap');
    }

    public function handleCategorySaved(CategorySaved $event): void
    {
        Cache::forget('sitemap');
    }
}
