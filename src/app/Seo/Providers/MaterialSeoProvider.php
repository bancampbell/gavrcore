<?php

namespace App\Seo\Providers;

use App\Seo\Contracts\SeoProviderInterface;
use App\Seo\DTO\MetaData;
use App\Models\Material;
use App\Services\SettingService;

class MaterialSeoProvider implements SeoProviderInterface
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function supports($entity): bool
    {
        return $entity instanceof Material;
    }

    public function generate($entity): MetaData
    {
        $settings = $this->settingService->getAllSettings();
        $siteName = $settings['site_name'] ?? config('app.name');

        $title = $entity->meta_title ?? "{$entity->title} | {$siteName}";
        $description = $entity->meta_description ?? $this->generateDescription($entity);
        $keywords = $entity->meta_keywords ?? ($settings['seo_keywords'] ?? '');

        return new MetaData(
            title: $title,
            description: $description,
            keywords: $keywords,
            ogTitle: $entity->meta_title ?? $entity->title,
            ogDescription: $entity->meta_description ?? $description,
            ogImage: $this->getOgImage($entity),
            ogType: 'article',
            canonical: route('page.show', $entity->slug),
        );
    }

    public function getType(): string
    {
        return 'material';
    }

    private function generateDescription(Material $material): string
    {
        if ($material->excerpt) {
            return $material->excerpt;
        }

        $content = strip_tags($material->content ?? '');
        return str()->limit($content, 160);
    }

    private function getOgImage(Material $material): ?string
    {
        // Здесь потом добавите получение изображения из медиа
        // Например: $material->getFirstMediaUrl('cover')
        return null;
    }
}
