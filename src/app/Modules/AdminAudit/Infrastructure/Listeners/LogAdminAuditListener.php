<?php

namespace App\Modules\AdminAudit\Infrastructure\Listeners;

use App\Modules\AdminAudit\Infrastructure\Models\AdminAuditLog;
use App\Modules\FormBuilder\Domain\Events\FormCreated;
use App\Modules\FormBuilder\Domain\Events\FormDeleted;
use App\Modules\FormBuilder\Domain\Events\FormUpdated;
use App\Modules\MaterialManager\Domain\Events\MaterialCreated;
use App\Modules\MaterialManager\Domain\Events\MaterialDeleted;
use App\Modules\MaterialManager\Domain\Events\MaterialUpdated;
use App\Modules\UserManager\Domain\Events\UserCreated;
use App\Modules\UserManager\Domain\Events\UserDeleted;
use App\Modules\UserManager\Domain\Events\UserUpdated;

class LogAdminAuditListener
{
    public function handle(object $event): void
    {
        $log = new AdminAuditLog;

        match (true) {
            $event instanceof MaterialCreated => $log->fill([
                'action' => 'created',
                'subject_type' => 'material',
                'subject_id' => $event->material->id,
            ]),
            $event instanceof MaterialUpdated => $log->fill([
                'action' => 'updated',
                'subject_type' => 'material',
                'subject_id' => $event->material->id,
            ]),
            $event instanceof MaterialDeleted => $log->fill([
                'action' => 'deleted',
                'subject_type' => 'material',
                'subject_id' => $event->material->id,
            ]),
            $event instanceof UserCreated => $log->fill([
                'action' => 'created',
                'subject_type' => 'user',
                'subject_id' => $event->user->id?->value,
            ]),
            $event instanceof UserUpdated => $log->fill([
                'action' => 'updated',
                'subject_type' => 'user',
                'subject_id' => $event->user->id?->value,
            ]),
            $event instanceof UserDeleted => $log->fill([
                'action' => 'deleted',
                'subject_type' => 'user',
                'subject_id' => $event->user->id?->value,
            ]),
            $event instanceof FormCreated => $log->fill([
                'action' => 'created',
                'subject_type' => 'form',
                'subject_id' => $event->form->id,
            ]),
            $event instanceof FormUpdated => $log->fill([
                'action' => 'updated',
                'subject_type' => 'form',
                'subject_id' => $event->form->id,
            ]),
            $event instanceof FormDeleted => $log->fill([
                'action' => 'deleted',
                'subject_type' => 'form',
                'subject_id' => $event->formId,
            ]),
        };

        // Аудит не должен валить операцию: событие без идентификатора
        // субъекта (например, мок репозитория в юнит-тесте) пропускаем,
        // по образцу LogMaterialActivityListener::log (модель не найдена → return).
        if ($log->subject_id === null) {
            return;
        }

        $log->actor = auth()->id();
        $log->save();
    }
}
