<?php

namespace App\Modules\MaterialManager\Infrastructure\Services;

use App\Modules\MaterialManager\Domain\Services\ContentParserInterface;

class ContentParser implements ContentParserInterface
{
    public function extractFormIds(?string $content): array
    {
        if (!$content) {
            return [];
        }

        preg_match_all('/\[form id="(\d+)"\]/', $content, $matches);
        return array_map('intval', $matches[1] ?? []);
    }
}
