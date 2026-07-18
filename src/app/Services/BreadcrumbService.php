<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Material;

class BreadcrumbService
{
    /**
     * Генерация хлебных крошек для материала
     */
    public function forMaterial(Material $material): array
    {
        $breadcrumbs = [
            [
                'title' => 'Главная',
                'url' => route('home'),
            ],
        ];

        // Добавляем категорию, если есть
        if ($material->category) {
            $breadcrumbs[] = [
                'title' => $material->category->name,
                'url' => route('category.show', $material->category->slug),
            ];
        }

        // Текущая страница (без ссылки)
        $breadcrumbs[] = [
            'title' => $material->title,
            'url' => null,
        ];

        return $breadcrumbs;
    }

    /**
     * Генерация хлебных крошек для категории
     */
    public function forCategory(Category $category): array
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

    /**
     * Генерация хлебных крошек для поиска
     */
    public function forSearch(string $query = null): array
    {
        $breadcrumbs = [
            [
                'title' => 'Главная',
                'url' => route('home'),
            ],
        ];

        $breadcrumbs[] = [
            'title' => $query ? "Поиск: {$query}" : 'Поиск',
            'url' => null,
        ];

        return $breadcrumbs;
    }
}
