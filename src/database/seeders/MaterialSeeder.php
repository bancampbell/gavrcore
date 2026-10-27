<?php

namespace Database\Seeders;

use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use App\Modules\FormBuilder\Infrastructure\Models\FormModel;
use App\Modules\MaterialManager\Infrastructure\Models\MaterialModel;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MaterialSeeder extends Seeder
{
    /**
     * Фиксированный набор материалов: slug строится только из названия
     * (без счётчика), поэтому повторный seed всегда даёт те же URL.
     * Сидер идемпотентен: материалы создаются через firstOrCreate по slug,
     * повторный запуск не дублирует записи и не падает на unique-индексе.
     * Существующие материалы (в т.ч. изменённые вручную) не перезаписываются.
     * Названия должны оставаться уникальными — на slug есть unique-индекс
     * через alias. Доступ: в основном public, один registered и один
     * special — для проверки ограничения доступа.
     */
    private const MATERIALS = [
        ['title' => 'Программа долгосрочных сбережений (ПДС)', 'state' => 'published', 'access' => 'public', 'views' => 1520, 'published_at' => '2026-01-15 09:00:00'],
        ['title' => 'Финансовая (бухгалтерская) отчетность за 2025 год', 'state' => 'published', 'access' => 'public', 'views' => 2310, 'published_at' => '2026-01-28 10:30:00'],
        ['title' => 'Предложение о размере цен (тарифов)', 'state' => 'published', 'access' => 'public', 'views' => 890, 'published_at' => '2026-02-05 11:00:00'],
        ['title' => 'Новые правила регулирования', 'state' => 'published', 'access' => 'public', 'views' => 1750, 'published_at' => '2026-02-17 12:00:00'],
        ['title' => 'Изменения в законодательстве', 'state' => 'published', 'access' => 'public', 'views' => 640, 'published_at' => '2026-03-02 09:15:00'],
        ['title' => 'Годовой отчет за 2025 год', 'state' => 'published', 'access' => 'public', 'views' => 3120, 'published_at' => '2026-03-20 13:00:00'],
        ['title' => 'План мероприятий на 2026 год', 'state' => 'published', 'access' => 'public', 'views' => 420, 'published_at' => '2026-04-01 08:45:00'],
        ['title' => 'Анализ рынка электроэнергии', 'state' => 'published', 'access' => 'public', 'views' => 1280, 'published_at' => '2026-04-14 15:30:00'],
        ['title' => 'Стратегия развития компании', 'state' => 'published', 'access' => 'public', 'views' => 960, 'published_at' => '2026-05-06 10:00:00'],
        ['title' => 'Бюджет на 2026 год', 'state' => 'published', 'access' => 'public', 'views' => 710, 'published_at' => '2026-05-22 14:20:00'],
        ['title' => 'Кадровые изменения', 'state' => 'published', 'access' => 'public', 'views' => 380, 'published_at' => '2026-06-10 09:00:00'],
        ['title' => 'Инвестиционные проекты', 'state' => 'published', 'access' => 'public', 'views' => 1140, 'published_at' => '2026-06-25 16:00:00'],
        ['title' => 'Тарифы на услуги по передаче электрической энергии', 'state' => 'published', 'access' => 'public', 'views' => 2680, 'published_at' => '2026-07-08 11:30:00'],
        ['title' => 'Раскрытие информации для акционеров', 'state' => 'published', 'access' => 'registered', 'views' => 540, 'published_at' => '2026-07-19 10:10:00'],
        ['title' => 'Общественные и управленческие недвижимость', 'state' => 'published', 'access' => 'special', 'views' => 220, 'published_at' => '2026-08-03 09:30:00'],
        ['title' => 'Инструкция для зарегистрированных пользователей', 'state' => 'draft', 'access' => 'registered', 'views' => 0, 'published_at' => null],
        ['title' => 'Архивный материал 2024 года', 'state' => 'archived', 'access' => 'public', 'views' => 4890, 'published_at' => '2024-12-20 12:00:00'],
        ['title' => 'Черновик годового отчета', 'state' => 'draft', 'access' => 'public', 'views' => 0, 'published_at' => null],
    ];

    public function run(): void
    {
        $categories = CategoryModel::orderBy('id')->pluck('id');
        $users = User::orderBy('id')->pluck('id');

        // Если нет категорий или пользователей — пропускаем
        if ($categories->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach (self::MATERIALS as $i => $material) {
            $title = $material['title'];
            $slug = Str::slug($title);

            MaterialModel::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'alias' => $slug,
                    'content' => '<h2>'.$title.'</h2><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p><p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>',
                    'category_id' => $categories[$i % $categories->count()],
                    'user_id' => $users[$i % $users->count()],
                    'state' => $material['state'],
                    'access' => $material['access'],
                    'views' => $material['views'],
                    'published_at' => $material['published_at'],
                ]
            );
        }

        // Демо-страница с формой обратной связи: показывает механизм
        // form-шорткодов (FormRenderer/ShortcodeRenderer). Требует, чтобы
        // FormsSeeder отработал раньше (DatabaseSeeder вызывает его выше).
        $feedbackForm = FormModel::where('alias', 'feedback')->first();

        if ($feedbackForm) {
            $title = 'Обратная связь';

            MaterialModel::firstOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'title' => $title,
                    'alias' => Str::slug($title),
                    'content' => '<h2>Обратная связь</h2><p>Заполните форму ниже, и мы ответим вам в ближайшее время.</p>[form id="'.$feedbackForm->id.'"]',
                    'category_id' => $categories[0],
                    'user_id' => $users[0],
                    'state' => 'published',
                    'access' => 'public',
                    'views' => 0,
                    'published_at' => '2026-09-01 09:00:00',
                ]
            );
        }
    }
}
