<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Uncategorized', 'description' => 'Без категории'],
            ['name' => 'Новости', 'description' => 'Новости компании'],
            ['name' => 'Тарифы на услуги по передаче электрической энергии', 'description' => 'Тарифы и регулирование'],
            ['name' => 'Раскрытие информации за 2026', 'description' => 'Отчетность и раскрытие информации'],
            ['name' => 'Документы', 'description' => 'Нормативные документы'],
            ['name' => 'Финансовая отчетность', 'description' => 'Бухгалтерская отчетность'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['alias' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
