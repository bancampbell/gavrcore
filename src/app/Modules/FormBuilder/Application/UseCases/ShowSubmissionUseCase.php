<?php

namespace App\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Domain\Entities\FormSubmission;
use App\Modules\FormBuilder\Domain\Repositories\FormSubmissionRepositoryInterface;

class ShowSubmissionUseCase
{
    public function __construct(protected FormSubmissionRepositoryInterface $repository) {}

    /**
     * @param  int|null  $viewerId  id текущего пользователя кабинета.
     *                              null — вызов из админки: владение не
     *                              проверяется, поведение прежнее.
     */
    public function execute(int $id, ?int $viewerId = null): ?FormSubmission
    {
        $submission = $this->repository->find($id);

        if (! $submission) {
            return null;
        }

        // Проверка владения ДО markAsRead: раньше чужая заявка по прямому
        // URL помечалась прочитанной (мутация до авторизации) — любой
        // залогиненный пользователь сбрасывал read_at чужих заявок перебором id.
        if ($viewerId !== null && $submission->userId !== $viewerId) {
            return null;
        }

        if (! $submission->isRead()) {
            $this->repository->markAsRead($id);
            $submission->markAsRead();
        }

        return $submission;
    }
}
