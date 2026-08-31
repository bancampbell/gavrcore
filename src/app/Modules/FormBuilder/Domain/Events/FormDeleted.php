<?php

namespace App\Modules\FormBuilder\Domain\Events;

class FormDeleted
{
    public function __construct(public readonly int $formId) {}
}