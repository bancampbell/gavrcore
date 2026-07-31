<?php

namespace Modules\Shop\Application\UseCases\Cart;

use Modules\Shop\Domain\Entities\Cart;
use Modules\Shop\Domain\Entities\CartItem;
use Modules\Shop\Domain\Repositories\CartRepositoryInterface;
use Modules\Shop\Domain\Repositories\ProductRepositoryInterface;

/**
 * Добавить товар в корзину
 */
class AddToCartUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepo,
        private CartRepositoryInterface $cartRepo
    ) {}

    public function execute(string $sessionId, string $productId, int $quantity): Cart
    {
        $product = $this->productRepo->findOrFail($productId);
        if ($quantity > $product->getStock()) {
            throw new \InvalidArgumentException('Insufficient stock');
        }

        $cart = $this->cartRepo->get($sessionId) ?? new Cart($sessionId);
        $cart->addItem(new CartItem($productId, $product->getPrice()->getAmount(), $quantity));
        $this->cartRepo->save($cart);
        return $cart;
    }
}
