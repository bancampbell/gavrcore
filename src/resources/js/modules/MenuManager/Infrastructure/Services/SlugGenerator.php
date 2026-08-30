<?php

namespace App\Modules\MenuManager\Infrastructure\Services;

use App\Modules\MenuManager\Domain\Services\SlugGeneratorInterface;
use Illuminate\Support\Str;

class SlugGenerator implements SlugGeneratorInterface
{
    public function generate(string $title, ?string $existingAlias = null): string
    {
        if ($existingAlias) return $existingAlias;
        return Str::slug($title);
    }
}