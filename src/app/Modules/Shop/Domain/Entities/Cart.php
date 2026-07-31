<?php

namespace Modules\Shop\Domain\Entities;

/**
 * Корзина покупок (сессионная сущность).
 * Хранит список товаров (CartItem) и идентификатор сессии.
 */
class Cart
{
    private string $sessionId;
    /** @var CartItem[] */
    private array $items = [];

    public function __construct(string $sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Добавить товар в корзину.
     * Если товар уже есть — увеличивает количество.
     */
    public function addItem(CartItem $item): void
    {
        foreach ($this->items as $existing) {
            if ($existing->getProductId() === $item->getProductId()) {
                $existing->increaseQuantity($item->getQuantity());
                return;
            }
        }
        $this->items[] = $item;
    }

    /** @return CartItem[] */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /** Очистить корзину */
    public function clear(): void
    {
        $this->items = [];
    }

    /** Общее количество товаров (сумма quantity) */
    public function getTotalQuantity(): int
    {
        return array_sum(array_map(fn(CartItem $item) => $item->getQuantity(), $this->items));
    }
}
