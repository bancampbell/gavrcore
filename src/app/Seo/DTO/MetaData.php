<?php

namespace App\Seo\DTO;

class MetaData
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly ?string $keywords = null,
        public readonly ?string $ogTitle = null,
        public readonly ?string $ogDescription = null,
        public readonly ?string $ogImage = null,
        public readonly ?string $ogType = 'website',
        public readonly ?string $twitterCard = 'summary_large_image',
        public readonly ?array $schema = null,
        public readonly ?array $breadcrumbs = null,
        public readonly ?string $canonical = null,
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $this->keywords,
            'og_title' => $this->ogTitle ?? $this->title,
            'og_description' => $this->ogDescription ?? $this->description,
            'og_image' => $this->ogImage,
            'og_type' => $this->ogType,
            'twitter_card' => $this->twitterCard,
            'schema' => $this->schema ? json_encode($this->schema, JSON_UNESCAPED_UNICODE) : null,
            'breadcrumbs' => $this->breadcrumbs,
            'canonical' => $this->canonical,
        ];
    }
}
