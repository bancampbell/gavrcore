<?php

namespace App\Modules\MaterialManager\Domain\ValueObjects;

final readonly class MaterialUpdateData
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
}
