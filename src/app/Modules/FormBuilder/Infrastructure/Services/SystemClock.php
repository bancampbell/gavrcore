<?php

namespace App\Modules\FormBuilder\Infrastructure\Services;

use App\Modules\FormBuilder\Domain\Services\ClockInterface;

class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}