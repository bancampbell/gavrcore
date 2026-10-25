<?php

namespace App\Modules\FormBuilder\Database\Seeders;

use App\Modules\FormBuilder\Domain\Entities\FormSubmission;
use App\Modules\FormBuilder\Infrastructure\Models\FormModel;
use App\Modules\FormBuilder\Infrastructure\Models\FormSubmissionModel;
use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Database\Seeder;

class FormSubmissionsSeeder extends Seeder
{
    public function run(): void
    {
        $form = FormModel::where('alias', 'feedback')->first();

        if (! $form) {
            return;
        }

        $admin = UserModel::where('email', 'admin@example.com')->first();

        $submissions = [
            [
                'data' => [
                    'name' => 'Администратор',
                    'email' => 'admin@example.com',
                    'subject' => 'Не приходит почта',
                    'message' => 'Проверьте, пожалуйста, настройку SMTP — уведомления не уходят.',
                ],
                'status' => FormSubmission::STATUS_COMPLETED,
                'user_id' => $admin?->id,
                'read_at' => now(),
            ],
            [
                'data' => [
                    'name' => 'Администратор',
                    'email' => 'admin@example.com',
                    'subject' => 'Добавить раздел FAQ',
                    'message' => 'Просьба добавить на сайт раздел с частыми вопросами.',
                ],
                'status' => FormSubmission::STATUS_IN_PROGRESS,
                'user_id' => $admin?->id,
                'read_at' => now(),
            ],
            [
                'data' => [
                    'name' => 'Администратор',
                    'email' => 'admin@example.com',
                    'subject' => 'Ошибка в личном кабинете',
                    'message' => 'При смене темы оформления страница перезагружается дважды.',
                ],
                'status' => FormSubmission::STATUS_NEW,
                'user_id' => $admin?->id,
            ],
            [
                'data' => [
                    'name' => 'Гость',
                    'email' => 'guest@example.com',
                    'subject' => 'Вопрос о сотрудничестве',
                    'message' => 'Хотели бы обсудить размещение материалов.',
                ],
                'status' => FormSubmission::STATUS_NEW,
                'user_id' => null,
            ],
        ];

        foreach ($submissions as $submission) {
            // Идемпотентность: заявка с такой темой от сидера уже есть — пропускаем,
            // чтобы повторный запуск не плодил дубли.
            $exists = FormSubmissionModel::where('form_id', $form->id)
                ->where('data->subject', $submission['data']['subject'])
                ->where('meta->source', 'seeder')
                ->exists();

            if ($exists) {
                continue;
            }

            FormSubmissionModel::create([
                'form_id' => $form->id,
                'user_id' => $submission['user_id'],
                'status' => $submission['status'],
                'data' => $submission['data'],
                'meta' => ['source' => 'seeder'],
                'read_at' => $submission['read_at'] ?? null,
            ]);
        }

        $count = FormSubmissionModel::where('form_id', $form->id)->count();

        $form->update(['submissions_count' => $count]);
    }
}
