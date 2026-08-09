<?php

namespace App\Modules\MaterialManager\Application\DTO;

use App\Modules\MaterialManager\Domain\ValueObjects\MaterialAccess;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialStatus;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialUpdateData;
use App\Modules\MaterialManager\Domain\ValueObjects\NotSet;

final readonly class UpdateMaterialData
{
    public function __construct(
        public string|NotSet|null $title,
        public string|NotSet|null $slug,
        public string|NotSet|null $content,
        public int|NotSet|null $categoryId,
        public MaterialStatus|NotSet|null $status,
        public MaterialAccess|NotSet|null $access,
        public bool|NotSet|null $showOnHomepage,
        public bool|NotSet|null $showDate,
        public bool|NotSet|null $showAuthor,
        public bool|NotSet|null $showCategory,
        public bool|NotSet|null $showViews,
        public bool|NotSet|null $useGlobalSettings,
        public string|NotSet|null $template,
        public string|NotSet|null $metaTitle,
        public string|NotSet|null $metaDescription,
        public string|NotSet|null $metaKeywords,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: self::resolveString($data, 'title'),
            slug: self::resolveString($data, 'slug'),
            content: self::resolveString($data, 'content'),
            categoryId: self::resolveInt($data, 'category_id'),
            status: self::resolveEnum($data, 'state', MaterialStatus::class),
            access: self::resolveEnum($data, 'access', MaterialAccess::class),
            showOnHomepage: self::resolveBool($data, 'show_on_homepage'),
            showDate: self::resolveBool($data, 'show_date'),
            showAuthor: self::resolveBool($data, 'show_author'),
            showCategory: self::resolveBool($data, 'show_category'),
            showViews: self::resolveBool($data, 'show_views'),
            useGlobalSettings: self::resolveBool($data, 'use_global_settings'),
            template: self::resolveString($data, 'template'),
            metaTitle: self::resolveString($data, 'meta_title'),
            metaDescription: self::resolveString($data, 'meta_description'),
            metaKeywords: self::resolveString($data, 'meta_keywords'),
        );
    }

    public function toDomain(): MaterialUpdateData
    {
        return new MaterialUpdateData(
            title: $this->title,
            slug: $this->slug,
            content: $this->content,
            categoryId: $this->categoryId,
            status: $this->status,
            access: $this->access,
            showOnHomepage: $this->showOnHomepage,
            showDate: $this->showDate,
            showAuthor: $this->showAuthor,
            showCategory: $this->showCategory,
            showViews: $this->showViews,
            useGlobalSettings: $this->useGlobalSettings,
            template: $this->template,
            metaTitle: $this->metaTitle,
            metaDescription: $this->metaDescription,
            metaKeywords: $this->metaKeywords,
        );
    }

    private static function resolveString(array $data, string $key): string|NotSet|null
    {
        if (!array_key_exists($key, $data)) {
            return NotSet::instance();
        }
        $value = $data[$key];
        return $value === null ? null : (string) $value;
    }

    private static function resolveInt(array $data, string $key): int|NotSet|null
    {
        if (!array_key_exists($key, $data)) {
            return NotSet::instance();
        }
        $value = $data[$key];
        if ($value === null) {
            return null;
        }
        $int = filter_var($value, FILTER_VALIDATE_INT);
        if ($int === false) {
            throw new \InvalidArgumentException("Invalid integer for {$key}");
        }
        return $int;
    }

    private static function resolveBool(array $data, string $key): bool|NotSet|null
    {
        if (!array_key_exists($key, $data)) {
            return NotSet::instance();
        }
        $value = $data[$key];
        if ($value === null) {
            return null;
        }
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

    private static function resolveEnum(array $data, string $key, string $enumClass): \BackedEnum|NotSet|null
    {
        if (!array_key_exists($key, $data)) {
            return NotSet::instance();
        }
        $value = $data[$key];
        if ($value === null) {
            return null;
        }
        return $enumClass::tryFrom((string) $value) ?? throw new \InvalidArgumentException("Invalid enum value for {$key}");
    }
}
