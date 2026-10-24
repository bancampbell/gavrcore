<?php

namespace App\Modules\SeoManager\Application\Services;

use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;

class BreadcrumbService
{
    /**
     * Генерация хлебных крошек для категории
     */
    public function forCategory(CategoryModel $category): array
    {
        $breadcrumbs = [
            [
                'title' => 'Главная',
                'url' => route('home'),
            ],
        ];

        // Текущая категория (без ссылки)
        $breadcrumbs[] = [
            'title' => $category->name,
            'url' => null,
        ];

        return $breadcrumbs;
    }

    /**
     * Генерация хлебных крошек для главной страницы
     */
    public function forHome(): array
    {
        return [
            [
                'title' => 'Главная',
                'url' => null,
            ],
        ];
    }
}
