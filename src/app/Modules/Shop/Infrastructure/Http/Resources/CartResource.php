<?php

namespace Modules\Shop\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Shop\Domain\Entities\Cart;
use Modules\Shop\Domain\Services\PriceService;

/** @mixin Cart */
class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $priceService = app(PriceService::class);
        $items = array_map(
            fn($item) => [
                'product_id' => $item->getProductId(),
                'price' => $item->getPrice(),
                'quantity' => $item->getQuantity(),
                'total' => $item->getTotal(),
            ],
            $this->getItems()
        );

        return [
            'session_id' => $this->getSessionId(),
            'items' => $items,
            'total' => $priceService->calculateTotal($this->getItems()),
            'total_formatted' => number_format($priceService->calculateTotal($this->getItems()) / 100, 2, '.', ' ') . ' ₽',
            'total_quantity' => $this->getTotalQuantity(),
        ];
    }
}
