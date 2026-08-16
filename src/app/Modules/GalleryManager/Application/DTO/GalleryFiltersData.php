<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\DTO;

class GalleryFiltersData
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $type = null,
        public readonly ?bool $status = null,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            search: $request->input('search'),
            type: $request->input('type'),
            status: $request->has('status') ? (bool) $request->input('status') : null,
        );
    }
}
