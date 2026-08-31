<?php

namespace App\Modules\FormBuilder\Domain\Events;

use App\Modules\FormBuilder\Domain\Entities\FormSubmission;

class SubmissionRead
{
    public function __construct(public readonly FormSubmission $submission) {}
}