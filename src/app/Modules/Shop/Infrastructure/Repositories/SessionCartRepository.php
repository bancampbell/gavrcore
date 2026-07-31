<?php

namespace Modules\Shop\Infrastructure\Repositories;

use Modules\Shop\Domain\Entities\Cart;
use Modules\Shop\Domain\Entities\CartItem;
use Modules\Shop\Domain\Repositories\CartRepositoryInterface;

/**
 * Реализация корзины через сессию (сериализуется в массив).
 */
class SessionCartRepository implements CartRepositoryInterface
{
    public function get(string $sessionId): ?Cart
    {
        $data = session()->get("cart_{$sessionId}");
        if (!$data) {
            return null;
        }
        return $this->hydrate($data);
    }

    public function save(Cart $cart): void
    {
        $data = $this->extract($cart);
        session()->put("cart_{$cart->getSessionId()}", $data);
    }

    public function clear(string $sessionId): void
    {
        session()->forget("cart_{$sessionId}");
    }

    /**
     * Восстановить Cart из массива.
     */
    private function hydrate(array $data): Cart
    {
        $cart = new Cart($data['sessionId']);
        foreach ($data['items'] as $itemData) {
            $cart->addItem(new CartItem(
                $itemData['productId'],
                $itemData['price'],
                $itemData['quantity']
            ));
        }
        return $cart;
    }

    /**
     * Преобразовать Cart в массив для сессии.
     */
    private function extract(Cart $cart): array
    {
        $items = array_map(
            fn(CartItem $item) => [
                'productId' => $item->getProductId(),
                'price' => $item->getPrice(),
                'quantity' => $item->getQuantity(),
            ],
            $cart->getItems()
        );
        return [
            'sessionId' => $cart->getSessionId(),
            'items' => $items,
        ];
    }
}
