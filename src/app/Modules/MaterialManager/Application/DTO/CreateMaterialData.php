<?php

namespace App\Modules\MaterialManager\Application\DTO;

use App\Modules\MaterialManager\Domain\ValueObjects\MaterialAccess;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialStatus;

final readonly class CreateMaterialData
{
    public function __construct(
        public string $title,
        public ?string $slug,
        public ?string $content,
        public ?int $categoryId,
        public int $userId,
        public MaterialStatus $status,
        public MaterialAccess $access,
        public bool $showOnHomepage,
        public bool $showDate,
        public bool $showAuthor,
        public bool $showCategory,
        public bool $showViews,
        public bool $useGlobalSettings,
        public ?string $metaTitle,
        public ?string $metaDescription,
        public ?string $metaKeywords,
    ) {}

    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            title: (string) ($data['title'] ?? ''),
            slug: isset($data['slug']) ? (string) $data['slug'] : null,
            content: isset($data['content']) ? (string) $data['content'] : null,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            userId: $userId,
            status: MaterialStatus::tryFrom($data['state'] ?? 'draft') ?? MaterialStatus::DRAFT,
            access: MaterialAccess::tryFrom($data['access'] ?? 'public') ?? MaterialAccess::PUBLIC,
            showOnHomepage: self::castBool($data['show_on_homepage'] ?? false),
            showDate: self::castBool($data['show_date'] ?? true),
            showAuthor: self::castBool($data['show_author'] ?? true),
            showCategory: self::castBool($data['show_category'] ?? true),
            showViews: self::castBool($data['show_views'] ?? true),
            useGlobalSettings: self::castBool($data['use_global_settings'] ?? true),
            metaTitle: isset($data['meta_title']) ? (string) $data['meta_title'] : null,
            metaDescription: isset($data['meta_description']) ? (string) $data['meta_description'] : null,
            metaKeywords: isset($data['meta_keywords']) ? (string) $data['meta_keywords'] : null,
        );
    }

    private static function castBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value === 1;
        }
        if (is_string($value)) {
            return $value === '1' || strtolower($value) === 'true';
        }
        return false;
    }
}
