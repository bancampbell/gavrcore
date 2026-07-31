<?php

namespace Modules\Shop\Domain\Repositories;

use Modules\Shop\Domain\Entities\Cart;

interface CartRepositoryInterface
{
    public function get(string $sessionId): ?Cart;
    public function save(Cart $cart): void;
    public function clear(string $sessionId): void;
}
