<?php

namespace App\Modules\FormBuilder\Domain\Events;

use App\Modules\FormBuilder\Domain\Entities\Form;

class FormUpdated
{
    public function __construct(public readonly Form $form) {}
}