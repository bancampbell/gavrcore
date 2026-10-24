<?php

namespace App\Modules\MaterialManager\Application\Services;

class MaterialBreadcrumbService
{
    /**
     * Генерация хлебных крошек для материала
     */
    public function forMaterial($material): array
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
                'url' => route('category.show', ['slug' => $material->category->slug]),
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
