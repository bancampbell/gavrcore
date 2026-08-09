<?php

namespace App\Modules\MaterialManager\Infrastructure\Services;

use App\Modules\MaterialManager\Domain\Services\ClockInterface;

final readonly class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
