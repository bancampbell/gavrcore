<?php

namespace App\Modules\FormBuilder\Domain\Events;

use App\Modules\FormBuilder\Domain\Entities\Form;
use App\Modules\FormBuilder\Domain\Entities\FormSubmission;

class SubmissionCreated
{
    public function __construct(
        public readonly Form $form,
        public readonly FormSubmission $submission,
    ) {}
}