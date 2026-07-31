<?php

namespace Modules\Shop\Application\DTO;

/**
 * DTO для передачи данных о товаре между слоями
 */
class ProductData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $description,
        public readonly int $price, // в копейках
        public readonly string $sku,
        public readonly string $status, // draft|published|archived
        public readonly int $stock,
        public readonly ?string $categoryId = null
    ) {}
}
