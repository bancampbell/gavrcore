<?php

namespace Modules\Shop\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Shop\Domain\Entities\Product;

/** @mixin Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'price' => $this->getPrice()->getAmount(),
            'price_formatted' => $this->getPrice()->getFormatted(),
            'sku' => $this->getSku()->getValue(),
            'status' => $this->getStatus()->value,
            'stock' => $this->getStock(),
            'category_id' => $this->getCategoryId(),
        ];
    }
}
