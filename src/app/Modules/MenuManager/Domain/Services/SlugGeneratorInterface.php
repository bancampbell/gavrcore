<?php

namespace App\Modules\MenuManager\Domain\Services;

interface SlugGeneratorInterface
{
    public function generate(string $title, ?string $existingAlias = null): string;
}