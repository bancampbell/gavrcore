<?php

namespace Modules\Shop\Application\UseCases\Cart;

use Modules\Shop\Domain\Entities\Cart;
use Modules\Shop\Domain\Repositories\CartRepositoryInterface;
use Modules\Shop\Domain\Services\PriceService;

/**
 * Получить корзину по ID сессии
 */
class GetCartUseCase
{
    public function __construct(
        private CartRepositoryInterface $cartRepo,
        private PriceService $priceService
    ) {}

    public function execute(string $sessionId): Cart
    {
        $cart = $this->cartRepo->get($sessionId);
        if (!$cart) {
            $cart = new Cart($sessionId);
        }
        return $cart;
    }
}
