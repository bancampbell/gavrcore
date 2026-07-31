<?php

namespace Modules\Shop\Domain\Entities;

use Modules\Shop\Domain\ValueObjects\Money;

/**
 * Заказ (Order).
 * Хранит данные покупателя, состав заказа, статус и итоговую сумму.
 */
class Order
{
    private string $id;
    private ?string $userId;
    /** @var array{item: string, quantity: int, price: int} */
    private array $items;
    private Money $total;
    private string $status; // pending, paid, shipped, cancelled
    private string $email;
    private string $phone;
    private string $address;
    private ?string $comment;

    public function __construct(
        string $id,
        ?string $userId,
        array $items,
        Money $total,
        string $status,
        string $email,
        string $phone,
        string $address,
        ?string $comment = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->items = $items;
        $this->total = $total;
        $this->status = $status;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
        $this->comment = $comment;
    }

    public function getId(): string { return $this->id; }
    public function getUserId(): ?string { return $this->userId; }
    public function getItems(): array { return $this->items; }
    public function getTotal(): Money { return $this->total; }
    public function getStatus(): string { return $this->status; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): string { return $this->phone; }
    public function getAddress(): string { return $this->address; }
    public function getComment(): ?string { return $this->comment; }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
