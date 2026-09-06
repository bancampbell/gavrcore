<?php

namespace App\Modules\UserManager\Infrastructure\Services;

use App\Modules\UserManager\Domain\Services\SlugGeneratorInterface;
use Illuminate\Support\Str;

final class SlugGenerator implements SlugGeneratorInterface
{
    public function generate(string $value): string
    {
        return Str::slug($value);
    }
}
