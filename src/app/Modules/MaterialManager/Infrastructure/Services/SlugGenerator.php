<?php

namespace App\Modules\MaterialManager\Infrastructure\Services;

use App\Modules\MaterialManager\Domain\Services\SlugGeneratorInterface;
use Illuminate\Support\Str;

class SlugGenerator implements SlugGeneratorInterface
{
    public function generate(string $text): string
    {
        return Str::slug($text);
    }
}
