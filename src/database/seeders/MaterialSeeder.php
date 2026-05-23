<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $titles = [
            'Edit Category',
            'Общественные и управленческие недвижимость',
            'Программа долгосрочных сбережений (ПДС)',
            '2026 год',
            'Финансовая (бухгалтерская) отчетность',
            'Предложение о размере цен (тарифов)',
            'Новые правила регулирования',
            'Изменения в законодательстве',
            'Годовой отчет за 2025 год',
            'План мероприятий на 2026 год',
            'Анализ рынка',
            'Стратегия развития',
            'Бюджет на 2026 год',
            'Кадровые изменения',
            'Инвестиционные проекты',
        ];

        $categories = Category::pluck('id')->toArray();
        $users = User::pluck('id')->toArray();

        for ($i = 0; $i < 30; $i++) {
            $title = $titles[array_rand($titles)] . ' ' . ($i + 1);
            $states = ['published', 'draft', 'archived'];
            $access = ['public', 'registered', 'special'];

            Material::create([
                'title' => $title,
                'alias' => Str::slug($title) . '-' . ($i + 1),
                'content' => '<h2>' . $title . '</h2><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p><p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>',
                'category_id' => $categories[array_rand($categories)],
                'user_id' => $users[array_rand($users)],
                'state' => $states[array_rand($states)],
                'access' => $access[array_rand($access)],
                'views' => rand(0, 5000),
                'published_at' => now()->subDays(rand(0, 365)),
            ]);
        }
    }
}
