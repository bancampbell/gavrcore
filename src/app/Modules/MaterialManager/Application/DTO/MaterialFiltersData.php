<?php

namespace App\Modules\MaterialManager\Application\DTO;

use App\Modules\MaterialManager\Domain\ValueObjects\MaterialAccess;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialStatus;

final readonly class MaterialFiltersData
{
    public function __construct(
        public ?string $search,
        public ?MaterialStatus $status,
        public ?int $categoryId,
        public ?MaterialAccess $access,
        public ?int $authorId,
        public int $perPage = 10,
        public ?string $sort = null,
        public ?string $direction = null,
        public ?array $accessLevels = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            status: isset($data['state']) ? MaterialStatus::tryFrom($data['state']) : null,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            access: isset($data['access']) ? MaterialAccess::tryFrom($data['access']) : null,
            authorId: isset($data['author']) ? (int) $data['author'] : null,
            perPage: (int) ($data['per_page'] ?? 10),
            sort: $data['sort'] ?? null,
            direction: $data['direction'] ?? null,
            accessLevels: isset($data['access_levels']) && is_array($data['access_levels']) ? $data['access_levels'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'search' => $this->search,
            'state' => $this->status?->value,
            'category_id' => $this->categoryId,
            'access' => $this->access?->value,
            'author' => $this->authorId,
            'per_page' => $this->perPage,
            'sort' => $this->sort,
            'direction' => $this->direction,
            'access_levels' => $this->accessLevels,
        ];
    }
}
