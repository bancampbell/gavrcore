<?php

namespace Modules\Shop\Domain\Repositories;

use Modules\Shop\Domain\Entities\Order;

interface OrderRepositoryInterface
{
    public function save(Order $order): void;
    public function find(string $id): ?Order;
}
