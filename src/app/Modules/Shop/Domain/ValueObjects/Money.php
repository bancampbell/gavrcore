<?php

namespace Modules\Shop\Domain\ValueObjects;

class Money
{
    private int $amount; // в копейках

    public function __construct(int $amount)
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount must be non-negative');
        }
        $this->amount = $amount;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getFormatted(): string
    {
        return number_format($this->amount / 100, 2, '.', ' ') . ' ₽';
    }

    public function add(Money $other): self
    {
        return new self($this->amount + $other->getAmount());
    }

    public function multiply(int $multiplier): self
    {
        return new self($this->amount * $multiplier);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->getAmount();
    }
}
