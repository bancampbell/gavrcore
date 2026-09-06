<?php

namespace App\Modules\UserManager\Application\DTO;

/**
 * Обёртка «значение передано / не передано» для patch-семантики
 * (отличает null от «поле вообще не прислали»).
 *
 * @template T
 */
final class Optional
{
    private function __construct(
        private readonly bool $present,
        private readonly mixed $value = null,
    ) {
    }

    /**
     * @template TValue
     *
     * @param  TValue  $value
     *
     * @return self<TValue>
     */
    public static function of(mixed $value): self
    {
        return new self(true, $value);
    }

    /**
     * @return self<never>
     */
    public static function empty(): self
    {
        return new self(false);
    }

    public function isPresent(): bool
    {
        return $this->present;
    }

    /**
     * @return T
     */
    public function value(): mixed
    {
        return $this->value;
    }
}
