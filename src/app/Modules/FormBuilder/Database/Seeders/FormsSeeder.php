<?php

namespace App\Modules\FormBuilder\Database\Seeders;

use App\Modules\FormBuilder\Infrastructure\Models\FormModel;
use Illuminate\Database\Seeder;

class FormsSeeder extends Seeder
{
    public function run(): void
    {
        FormModel::firstOrCreate(
            ['alias' => 'feedback'],
            [
                'title' => 'Обратная связь',
                'description' => 'Форма обратной связи для личного кабинета',
                'fields' => [
                    ['name' => 'name', 'label' => 'Ваше имя', 'type' => 'text', 'required' => true],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['name' => 'subject', 'label' => 'Тема обращения', 'type' => 'text', 'required' => false],
                    ['name' => 'message', 'label' => 'Сообщение', 'type' => 'textarea', 'required' => true],
                ],
                'settings' => [],
                'notification_emails' => ['admin@example.com'],
                'status' => true,
                'is_dynamic' => false,
                'submissions_count' => 0,
            ]
        );

        FormModel::firstOrCreate(
            ['alias' => 'support-request'],
            [
                'title' => 'Заявка в поддержку',
                'description' => 'Черновик формы для заявок в техническую поддержку',
                'fields' => [
                    ['name' => 'name', 'label' => 'Ваше имя', 'type' => 'text', 'required' => true],
                    ['name' => 'phone', 'label' => 'Телефон', 'type' => 'tel', 'required' => false],
                    ['name' => 'message', 'label' => 'Описание проблемы', 'type' => 'textarea', 'required' => true],
                ],
                'settings' => [],
                'notification_emails' => [],
                'status' => false,
                'is_dynamic' => false,
                'submissions_count' => 0,
            ]
        );
    }
}
