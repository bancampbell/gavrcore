<?php

namespace App\Modules\MaterialManager\Domain\Services;

interface SlugGeneratorInterface
{
    public function generate(string $text): string;
}
