<?php

namespace App\Modules\FormBuilder\Domain\Services;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}