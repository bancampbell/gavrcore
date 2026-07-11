<?php

namespace App\Services;

use App\Contracts\FormSubmissionRepositoryInterface;
use App\Models\FormSubmission;
use App\Mail\FormSubmissionNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class FormSubmissionService
{
    public function __construct(
        protected FormSubmissionRepositoryInterface $repository
    ) {
    }

    /**
     * Получить список с пагинацией и фильтрацией
     */
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    /**
     * Найти по ID
     */
    public function find(int $id): ?FormSubmission
    {
        return $this->repository->find($id);
    }

    /**
     * Отметить как прочитанное
     */
    public function markAsRead(int $id): bool
    {
        return $this->repository->markAsRead($id);
    }

    /**
     * Удалить
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Получить количество непрочитанных
     */
    public function countUnread(): int
    {
        return $this->repository->countUnread();
    }

    /**
     * Создать новое обращение и отправить уведомление
     */
    public function store(array $data): FormSubmission
    {
        $submission = FormSubmission::create($data);

        // Отправляем уведомление
        $this->sendNotification($submission);

        return $submission;
    }

    /**
     * Отправить уведомление на почту
     */
    public function sendNotification(FormSubmission $submission): void
    {
        $form = $submission->form;

        // Получаем email(ы) из настроек формы
        $emails = $form->notification_emails ?? [];

        // Если email(ы) не указаны - не отправляем
        if (empty($emails)) {
            Log::info('Уведомление не отправлено: email(ы) не указаны для формы', [
                'form_id' => $form->id,
                'form_title' => $form->title,
            ]);
            return;
        }

        try {
            Mail::to($emails)->send(new FormSubmissionNotification($submission));

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
