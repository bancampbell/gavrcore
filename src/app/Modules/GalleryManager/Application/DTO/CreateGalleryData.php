<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\DTO;

use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;
use App\Modules\GalleryManager\Infrastructure\Http\Requests\CreateGalleryRequest;

class CreateGalleryData
{
    public function __construct(
        public readonly string $title,
        public readonly GalleryType $type,
        public readonly array $settings,
        public readonly bool $status,
    ) {}

    public static function fromRequest(CreateGalleryRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            title: $validated['title'],
            type: GalleryType::from($validated['type']),
            settings: $validated['settings'] ?? [],
            status: isset($validated['status']) ? filter_var($validated['status'], FILTER_VALIDATE_BOOLEAN) : true,
        );
    }
}
