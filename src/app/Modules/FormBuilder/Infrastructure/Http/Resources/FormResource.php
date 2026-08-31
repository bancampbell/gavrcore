<?php

namespace App\Modules\FormBuilder\Infrastructure\Http\Resources;

use App\Modules\FormBuilder\Domain\Entities\Form;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Form $form */
        $form = $this->resource;
        return [
            'id' => $form->id,
            'title' => $form->title,
            'alias' => $form->alias,
            'description' => $form->description,
            'fields' => $form->fields->toArray(),
            'settings' => $form->settings->toArray(),
            'notification_emails' => $form->notificationEmails,
            'status' => $form->status->value(),
            'is_dynamic' => $form->isDynamic,
            'submissions_count' => $form->submissionsCount,
            'created_at' => $form->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $form->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}