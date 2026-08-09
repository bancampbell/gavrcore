<?php

namespace App\Modules\MaterialManager\Application\DTO;

final readonly class PaginatedResult
{
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
        public int $lastPage,
        public ?int $from,
        public ?int $to,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            items: $data['items'] ?? [],
            total: (int) ($data['total'] ?? 0),
            perPage: (int) ($data['per_page'] ?? 10),
            currentPage: (int) ($data['current_page'] ?? 1),
            lastPage: (int) ($data['last_page'] ?? 1),
            from: isset($data['from']) ? (int) $data['from'] : null,
            to: isset($data['to']) ? (int) $data['to'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'data' => $this->items,
            'total' => $this->total,
            'per_page' => $this->perPage,
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage,
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}
