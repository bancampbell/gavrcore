<?php

namespace Modules\Shop\Application\UseCases\Cart;

use Modules\Shop\Domain\Repositories\CartRepositoryInterface;

/**
 * Очистить корзину
 */
class ClearCartUseCase
{
    public function __construct(
        private CartRepositoryInterface $cartRepo
    ) {}

    public function execute(string $sessionId): void
    {
        $this->cartRepo->clear($sessionId);
    }
}
