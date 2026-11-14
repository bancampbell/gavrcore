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

        // Родительская категория (уровень раздела), если есть
        if ($category->parent_id !== null && ($parent = $category->parent) !== null) {
            $breadcrumbs[] = [
                'title' => $parent->name,
                'url' => route('category.show', ['slug' => $parent->alias]),
            ];
        }

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
