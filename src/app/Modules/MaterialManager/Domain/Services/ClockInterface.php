<?php

namespace App\Modules\MaterialManager\Domain\Services;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
