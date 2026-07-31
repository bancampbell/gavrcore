<?php

namespace Modules\Shop\Domain\Entities;

use Modules\Shop\Domain\ValueObjects\Money;
use Modules\Shop\Domain\ValueObjects\Sku;
use Modules\Shop\Domain\ValueObjects\ProductStatus;

class Product
{
    private string $id;
    private string $name;
    private string $description;
    private Money $price;
    private Sku $sku;
    private ProductStatus $status;
    private int $stock;
    private ?string $categoryId;

    public function __construct(
        string $id,
        string $name,
        string $description,
        Money $price,
        Sku $sku,
        ProductStatus $status,
        int $stock,
        ?string $categoryId = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->sku = $sku;
        $this->status = $status;
        $this->stock = $stock;
        $this->categoryId = $categoryId;
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getPrice(): Money { return $this->price; }
    public function getSku(): Sku { return $this->sku; }
    public function getStatus(): ProductStatus { return $this->status; }
    public function getStock(): int { return $this->stock; }
    public function getCategoryId(): ?string { return $this->categoryId; }

    public function updateStock(int $newStock): void
    {
        if ($newStock < 0) {
            throw new \InvalidArgumentException('Stock cannot be negative');
        }
        $this->stock = $newStock;
    }
}
