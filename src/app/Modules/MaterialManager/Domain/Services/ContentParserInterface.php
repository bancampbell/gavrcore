<?php

namespace App\Modules\MaterialManager\Domain\Services;

interface ContentParserInterface
{
    public function extractFormIds(?string $content): array;
}
