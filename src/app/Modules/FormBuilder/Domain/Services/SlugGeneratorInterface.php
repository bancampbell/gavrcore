<?php

namespace App\Modules\FormBuilder\Domain\Services;

interface SlugGeneratorInterface
{
    public function generate(string $text): string;
}