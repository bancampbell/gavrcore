<?php

namespace Database\Seeders;

use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Uncategorized', 'alias' => 'uncategorized', 'description' => 'Без категории'],
            ['name' => 'Новости', 'alias' => 'novosti', 'description' => 'Новости компании'],
            ['name' => 'Тарифы на услуги по передаче электрической энергии', 'alias' => 'tarify-na-uslugi-po-peredace-elektriceskoi-energii', 'description' => 'Тарифы и регулирование'],
            ['name' => 'Раскрытие информации за 2026', 'alias' => 'raskrytie-informacii-za-2026', 'description' => 'Отчетность и раскрытие информации'],
            ['name' => 'Документы', 'alias' => 'dokumenty', 'description' => 'Нормативные документы'],
            ['name' => 'Финансовая отчетность', 'alias' => 'finansovaia-otcetnost', 'description' => 'Бухгалтерская отчетность'],
        ];

        foreach ($categories as $cat) {
            // Только создаём: существующие категории (в т.ч. изменённые
            // вручную is_active/description) повторный seed не трогает.
            CategoryModel::firstOrCreate(
                ['alias' => $cat['alias']],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
