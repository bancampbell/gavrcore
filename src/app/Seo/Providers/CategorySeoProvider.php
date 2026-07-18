<?php

namespace App\Seo\Providers;

use App\Seo\Contracts\SeoProviderInterface;
use App\Seo\DTO\MetaData;
use App\Models\Category;
use App\Services\SettingService;

class CategorySeoProvider implements SeoProviderInterface
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function supports($entity): bool
    {
        return $entity instanceof Category;
    }

    public function generate($entity): MetaData
    {
        $settings = $this->settingService->getAllSettings();
        $siteName = $settings['site_name'] ?? config('app.name');

        $title = $entity->meta_title ?? "{$entity->name} | {$siteName}";
        $description = $entity->meta_description ?? "Категория: {$entity->name}";

        return new MetaData(
            title: $title,
            description: $description,
            keywords: $entity->meta_keywords ?? ($settings['seo_keywords'] ?? ''),
            ogTitle: $entity->meta_title ?? $entity->name,
            ogDescription: $entity->meta_description ?? $description,
            ogType: 'website',
            canonical: route('category.show', $entity->slug),
        );
    }

    public function getType(): string
    {
        return 'category';
    }
}
