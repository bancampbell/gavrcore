<?php

namespace App\Modules\FormBuilder\Infrastructure\Http\Resources;

use App\Modules\FormBuilder\Domain\Entities\FormSubmission;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var FormSubmission $sub */
        $sub = $this->resource;
        return [
            'id' => $sub->id,
            'form_id' => $sub->formId,
            'user_id' => $sub->userId,
            'data' => $sub->data,
            'status' => $sub->status,
            'status_label' => $sub->getStatusLabel(),
            'status_color' => $sub->getStatusColor(),
            'meta' => $sub->meta,
            'read_at' => $sub->readAt?->format('Y-m-d H:i:s'),
            'created_at' => $sub->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $sub->updatedAt?->format('Y-m-d H:i:s'),
            'display_name' => $sub->getDisplayName(),
            'sender_name' => $sub->getSenderName(),
            'sender_email' => $sub->getSenderEmail(),
            'sender_phone' => $sub->getSenderPhone(),
            'content' => $sub->getContent(),
            'subject' => $sub->getSubject(),
        ];
    }
}