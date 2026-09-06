<?php

namespace App\Modules\UserManager\Domain\Services;

interface SlugGeneratorInterface
{
    /**
     * Генерирует URL-безопасный алиас из произвольной строки
     * (транслитерация кириллицы включена).
     */
    public function generate(string $value): string;
}
