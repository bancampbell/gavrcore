<?php

namespace Modules\Shop\Application\UseCases\Order;

use Modules\Shop\Domain\Entities\Order;
use Modules\Shop\Domain\Repositories\CartRepositoryInterface;
use Modules\Shop\Domain\Repositories\OrderRepositoryInterface;
use Modules\Shop\Domain\Services\PriceService;
use Modules\Shop\Domain\ValueObjects\Money;
use Modules\Shop\Application\DTO\OrderData;
use Illuminate\Support\Str;

class CreateOrderUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orderRepo,
        private CartRepositoryInterface $cartRepo,
        private PriceService $priceService
    ) {}

    public function execute(string $sessionId, OrderData $data): Order
    {
        $cart = $this->cartRepo->get($sessionId);
        if (!$cart || count($cart->getItems()) === 0) {
            throw new \InvalidArgumentException('Корзина пуста');
        }

        $total = $this->priceService->calculateTotal($cart->getItems());
        $items = array_map(
            fn($item) => [
                'product_id' => $item->getProductId(),
                'price' => $item->getPrice(),
                'quantity' => $item->getQuantity(),
            ],
            $cart->getItems()
        );

        $order = new Order(
            id: Str::uuid()->toString(),
            userId: null, // можно добавить авторизованного пользователя позже
            items: $items,
            total: new Money($total),
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
