<?php

namespace App\Modules\MaterialManager\Infrastructure\Listeners;

use App\Models\User;
use App\Modules\MaterialManager\Domain\Events\MaterialCreated;
use App\Modules\MaterialManager\Domain\Events\MaterialDeleted;
use App\Modules\MaterialManager\Domain\Events\MaterialForceDeleted;
use App\Modules\MaterialManager\Domain\Events\MaterialHomepageToggled;
use App\Modules\MaterialManager\Domain\Events\MaterialPublished;
use App\Modules\MaterialManager\Domain\Events\MaterialRestored;
use App\Modules\MaterialManager\Domain\Events\MaterialUnpublished;
use App\Modules\MaterialManager\Domain\Events\MaterialUpdated;
use App\Modules\MaterialManager\Infrastructure\Models\MaterialModel;
use Spatie\Activitylog\Facades\Activity;

class LogMaterialActivityListener
{
    public function handleMaterialCreated(MaterialCreated $event): void
    {
        $this->log(
            $event->material->id,
            'Создан материал: ' . $event->material->title,
            'created',
            $event->actorId
        );
    }

    public function handleMaterialUpdated(MaterialUpdated $event): void
    {
        $this->log(
            $event->material->id,
            'Обновлен материал: ' . $event->material->title,
            'updated',
            $event->actorId
        );
    }

    public function handleMaterialDeleted(MaterialDeleted $event): void
    {
        $this->log(
            $event->material->id,
            'Перемещен в корзину: ' . $event->material->title,
            'deleted',
            $event->actorId
        );
    }

    public function handleMaterialRestored(MaterialRestored $event): void
    {
        $this->log(
            $event->material->id,
            'Восстановлен из корзины: ' . $event->material->title,
            'restored',
            $event->actorId
        );
    }

    public function handleMaterialForceDeleted(MaterialForceDeleted $event): void
    {
        $causer = User::find($event->actorId);

        $activity = Activity::withProperties([
            'material_id' => $event->material->id,
            'event' => 'force_deleted',
            'material_title' => $event->material->title,
        ]);

        if ($causer !== null) {
            $activity = $activity->causedBy($causer);
        }

        $activity->log('Полностью удален материал: ' . $event->material->title);
    }

    public function handleMaterialPublished(MaterialPublished $event): void
    {
        $this->log(
            $event->material->id,
            'Опубликован материал: ' . $event->material->title,
            'published',
            $event->actorId
        );
    }

    public function handleMaterialUnpublished(MaterialUnpublished $event): void
    {
        $this->log(
            $event->material->id,
            'Снят с публикации: ' . $event->material->title,
            'unpublished',
            $event->actorId
        );
    }

    public function handleMaterialHomepageToggled(MaterialHomepageToggled $event): void
    {
        $status = $event->material->showOnHomepage ? 'добавлен на' : 'удален с';
        $this->log(
            $event->material->id,
            "Материал {$status} главной страницы: " . $event->material->title,
            'homepage_toggled',
            $event->actorId,
            ['show_on_homepage' => $event->material->showOnHomepage]
        );
    }

    private function log(int $materialId, string $description, string $event, int $actorId, array $properties = []): void
    {
        $model = MaterialModel::find($materialId);

        if (!$model) {
            return;
        }

        $causer = User::find($actorId);

        $activity = Activity::withProperties(array_merge($properties, [
            'material_id' => $materialId,
            'event' => $event,
        ]));

        if ($causer !== null) {
            $activity = $activity->causedBy($causer);
        }

        $activity->performedOn($model)->log($description);
    }
}
