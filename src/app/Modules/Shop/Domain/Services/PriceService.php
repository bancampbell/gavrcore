<?php

namespace Modules\Shop\Domain\Services;

use Modules\Shop\Domain\Entities\CartItem;

/**
 * Сервис для расчёта цен (сумма корзины, скидки и т.п.)
 */
class PriceService
{
    /**
     * Рассчитать общую стоимость корзины
     *
     * @param CartItem[] $items
     * @return int сумма в копейках
     */
    public function calculateTotal(array $items): int
    {
        return array_sum(
            array_map(fn(CartItem $item) => $item->getTotal(), $items)
        );
    }
}
