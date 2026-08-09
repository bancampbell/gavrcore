<?php

namespace App\Modules\MaterialManager\Infrastructure\Http\Resources;

use App\Modules\MaterialManager\Domain\Entities\Material;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Material
 */
class MaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Material $material */
        $material = $this->resource;

        return [
            'id' => $material->id,
            'title' => $material->title,
            'slug' => $material->slug,
            'alias' => $material->slug,
            'content' => $material->content,
            'category_id' => $material->categoryId,
            'user_id' => $material->userId,
            'state' => $material->status->value,
            'access' => $material->access->value,
            'views' => $material->views,
            'published_at' => $material->publishedAt?->format(\DateTime::ATOM),
            'featured' => $material->featured,
            'show_on_homepage' => $material->showOnHomepage,
            'show_date' => $material->showDate,
            'show_author' => $material->showAuthor,
            'show_category' => $material->showCategory,
            'show_views' => $material->showViews,
            'use_global_settings' => $material->useGlobalSettings,
            'template' => $material->template,
            'meta_title' => $material->metaTitle,
            'meta_description' => $material->metaDescription,
            'meta_keywords' => $material->metaKeywords,
            'created_at' => $material->createdAt?->format(\DateTime::ATOM),
            'updated_at' => $material->updatedAt?->format(\DateTime::ATOM),
            'deleted_at' => $material->deletedAt?->format(\DateTime::ATOM),

            'category' => $material->category?->toArray(),
            'user' => $material->user ? [
                'id' => $material->user->id,
                'name' => $material->user->name,
            ] : null,

            'status_label' => $material->status->label(),
            'access_label' => $material->access->label(),
            'status_color' => $material->status->color(),
        ];
    }
}
