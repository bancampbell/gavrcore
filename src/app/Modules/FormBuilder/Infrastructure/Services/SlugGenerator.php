<?php

namespace App\Modules\FormBuilder\Infrastructure\Services;

use App\Modules\FormBuilder\Domain\Services\SlugGeneratorInterface;
use Illuminate\Support\Str;

class SlugGenerator implements SlugGeneratorInterface
{
    public function generate(string $text): string
    {
        $alias = Str::slug($text);
        if (!empty($alias)) return $alias;

        $ruMap = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '',
            'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        ];

        $alias = strtr(mb_strtolower($text), $ruMap);
        $alias = preg_replace('/[^a-z0-9]+/', '-', $alias);
        return trim($alias, '-');
    }
}