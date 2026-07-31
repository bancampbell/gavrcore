<?php

namespace Modules\Shop\Infrastructure\Repositories;

use Modules\Shop\Domain\Entities\Order as DomainOrder;
use Modules\Shop\Domain\Repositories\OrderRepositoryInterface;
use Modules\Shop\Domain\ValueObjects\Money;
use App\Models\Order as EloquentOrder;

class OrderRepository implements OrderRepositoryInterface
{
    public function save(DomainOrder $order): void
    {
        $eloquent = EloquentOrder::find($order->getId()) ?? new EloquentOrder();
        $eloquent->id = $order->getId();
        $eloquent->user_id = $order->getUserId();
        $eloquent->items = $order->getItems();
        $eloquent->total = $order->getTotal()->getAmount();
        $eloquent->status = $order->getStatus();
        $eloquent->email = $order->getEmail();
        $eloquent->phone = $order->getPhone();
        $eloquent->address = $order->getAddress();
        $eloquent->comment = $order->getComment();
        $eloquent->save();
    }

    public function find(string $id): ?DomainOrder
    {
        $eloquent = EloquentOrder::find($id);
        return $eloquent ? $this->toDomain($eloquent) : null;
    }

    private function toDomain(EloquentOrder $eloquent): DomainOrder
    {
        return new DomainOrder(
            $eloquent->id,
            $eloquent->user_id,
            $eloquent->items,
            new Money($eloquent->total),
            $eloquent->status,
            $eloquent->email,
            $eloquent->phone,
            $eloquent->address,
            $eloquent->comment
        );
    }
}
