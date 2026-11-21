<?php

namespace App\Modules\FormBuilder\Domain\Events;

use App\Modules\FormBuilder\Domain\Entities\FormSubmission;

class SubmissionDeleted
{
    public function __construct(
        public readonly FormSubmission $submission,
        public readonly int $actorId,
    ) {}
}
