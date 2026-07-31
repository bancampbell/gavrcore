<?php

namespace Modules\Shop\Domain\Entities;

class CartItem
{
    public function __construct(
        private string $productId,
        private int $price,
        private int $quantity
    ) {}

    public function getProductId(): string { return $this->productId; }
    public function getPrice(): int { return $this->price; }
    public function getQuantity(): int { return $this->quantity; }

    public function increaseQuantity(int $amount): void
    {
        $this->quantity += $amount;
    }

    public function getTotal(): int
    {
        return $this->price * $this->quantity;
    }
}
