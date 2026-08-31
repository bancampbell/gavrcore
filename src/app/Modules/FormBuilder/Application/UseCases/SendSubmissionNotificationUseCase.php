<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Domain\Entities\Form;
use App\Modules\FormBuilder\Domain\Entities\FormSubmission;
use App\Modules\FormBuilder\Infrastructure\Mail\FormSubmissionNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSubmissionNotificationUseCase
{
    public function execute(Form $form, FormSubmission $submission): void
    {
        $emails = $form->notificationEmails;

        if (empty($emails)) {
            Log::info('Уведомление не отправлено: email(ы) не указаны для формы', [
                'form_id' => $form->id,
                'form_title' => $form->title,
            ]);
            return;
        }

        try {
            Mail::to($emails)->send(new FormSubmissionNotification($submission, $form));
            Log::info('Уведомление отправлено', [
                'form_id' => $form->id,
                'submission_id' => $submission->id,
                'emails' => $emails,
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка отправки уведомления', [
                'form_id' => $form->id,
                'submission_id' => $submission->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}