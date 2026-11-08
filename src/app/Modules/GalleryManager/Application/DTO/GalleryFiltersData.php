<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\DTO;

use Illuminate\Http\Request;

class GalleryFiltersData
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $type = null,
        public readonly ?bool $status = null,
        public readonly int $perPage = 10,
        public readonly int $page = 1,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->input('search'),
            type: $request->input('type'),
            status: $request->has('status')
                ? filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN)
                : null,
            perPage: (int) $request->input('per_page', 10),
            page: (int) $request->input('page', 1),
        );
    }

    public function toArray(): array
    {
        return [
            'search' => $this->search,
            'type' => $this->type,
            'status' => $this->status,
            'per_page' => $this->perPage,
            'page' => $this->page,
        ];
    }
}
