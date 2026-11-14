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

        // Добавляем родительскую категорию, если есть (иерархия раздела)
        if ($material->category && $material->category->parentAlias) {
            $breadcrumbs[] = [
                'title' => $material->category->parentName,
                'url' => route('category.show', ['slug' => $material->category->parentAlias]),
            ];
        }

        // Добавляем категорию, если есть
        if ($material->category) {
            $breadcrumbs[] = [
                'title' => $material->category->name,
                'url' => route('category.show', ['slug' => $material->category->alias]),
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
    public function forSearch(?string $query = null): array
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
