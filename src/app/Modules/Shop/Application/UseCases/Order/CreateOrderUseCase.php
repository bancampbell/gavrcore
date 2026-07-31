<?php

namespace Modules\Shop\Application\UseCases\Order;

use Modules\Shop\Domain\Entities\Order;
use Modules\Shop\Domain\Repositories\CartRepositoryInterface;
use Modules\Shop\Domain\Repositories\OrderRepositoryInterface;
use Modules\Shop\Domain\Services\PriceService;
use Modules\Shop\Application\DTO\OrderData;
use Illuminate\Support\Str;

/**
 * Создать заказ из корзины
 */
class CreateOrderUseCase
{
    public function __construct(
        private CartRepositoryInterface $cartRepo,
        private OrderRepositoryInterface $orderRepo,
        private PriceService $priceService
    ) {}

    public function execute(string $sessionId, OrderData $data, ?string $userId = null): Order
    {
        $cart = $this->cartRepo->get($sessionId);
        if (!$cart || empty($cart->getItems())) {
            throw new \InvalidArgumentException('Cart is empty');
        }

        $total = $this->priceService->calculateTotal($cart->getItems());
        $items = array_map(fn($item) => [
            'product_id' => $item->getProductId(),
            'quantity' => $item->getQuantity(),
            'price' => $item->getPrice(),
        ], $cart->getItems());

        $order = new Order(
            id: Str::uuid()->toString(),
            userId: $userId,
            items: $items,
            total: new \Modules\Shop\Domain\ValueObjects\Money($total),
            status: 'pending',
            email: $data->email,
            phone: $data->phone,
            address: $data->address,
            comment: $data->comment
        );

        $this->orderRepo->save($order);
        $this->cartRepo->clear($sessionId);

        return $order;
    }
}
