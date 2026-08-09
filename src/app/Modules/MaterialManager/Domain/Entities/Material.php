<?php

namespace App\Modules\MaterialManager\Domain\Entities;

use App\Modules\MaterialManager\Domain\ValueObjects\MaterialAccess;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialStatus;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialUpdateData;
use App\Modules\MaterialManager\Domain\ValueObjects\CategorySummaryVO;
use App\Modules\MaterialManager\Domain\ValueObjects\AuthorSummaryVO;
use App\Modules\MaterialManager\Domain\ValueObjects\NotSet;

class Material
{
    public function __construct(
        public readonly ?int $id,
        public string $title,
        public string $slug,
        public ?string $content,
        public ?int $categoryId,
        public int $userId,
        public MaterialStatus $status,
        public MaterialAccess $access,
        public int $views,
        public ?\DateTimeInterface $publishedAt,
        public bool $featured,
        public bool $showOnHomepage,
        public bool $showDate,
        public bool $showAuthor,
        public bool $showCategory,
        public bool $showViews,
        public bool $useGlobalSettings,
        public ?string $template,
        public ?string $metaTitle,
        public ?string $metaDescription,
        public ?string $metaKeywords,
        public ?\DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
        public ?\DateTimeInterface $deletedAt = null,
        public ?CategorySummaryVO $category = null,
        public ?AuthorSummaryVO $user = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'category_id' => $this->categoryId,
            'user_id' => $this->userId,
            'state' => $this->status->value,
            'access' => $this->access->value,
            'views' => $this->views,
            'published_at' => $this->publishedAt?->format(\DateTime::ATOM),
            'featured' => $this->featured,
            'show_on_homepage' => $this->showOnHomepage,
            'show_date' => $this->showDate,
            'show_author' => $this->showAuthor,
            'show_category' => $this->showCategory,
            'show_views' => $this->showViews,
            'use_global_settings' => $this->useGlobalSettings,
            'template' => $this->template,
            'meta_title' => $this->metaTitle,
            'meta_description' => $this->metaDescription,
            'meta_keywords' => $this->metaKeywords,
            'created_at' => $this->createdAt?->format(\DateTime::ATOM),
            'updated_at' => $this->updatedAt?->format(\DateTime::ATOM),
            'deleted_at' => $this->deletedAt?->format(\DateTime::ATOM),
            'category' => $this->category?->toArray(),
            'user' => $this->user?->toArray(),
        ];
    }

    public static function create(
        string $title,
        int $userId,
        string $slug,
        ?int $categoryId = null,
        ?string $content = null,
        MaterialStatus $status = MaterialStatus::DRAFT,
        MaterialAccess $access = MaterialAccess::PUBLIC,
        bool $showOnHomepage = false,
        bool $showDate = true,
        bool $showAuthor = true,
        bool $showCategory = true,
        bool $showViews = true,
        bool $useGlobalSettings = true,
        ?string $metaTitle = null,
        ?string $metaDescription = null,
        ?string $metaKeywords = null,
        ?\DateTimeInterface $createdAt = null,
    ): self {
        if ($slug === '') {
            throw new \InvalidArgumentException('Slug cannot be empty');
        }

        return new self(
            id: null,
            title: $title,
            slug: $slug,
            content: $content,
            categoryId: $categoryId,
            userId: $userId,
            status: $status,
            access: $access,
            views: 0,
            publishedAt: $status === MaterialStatus::PUBLISHED ? ($createdAt ?? new \DateTimeImmutable()) : null,
            featured: false,
            showOnHomepage: $showOnHomepage,
            showDate: $showDate,
            showAuthor: $showAuthor,
            showCategory: $showCategory,
            showViews: $showViews,
            useGlobalSettings: $useGlobalSettings,
            template: null,
            metaTitle: $metaTitle,
            metaDescription: $metaDescription,
            metaKeywords: $metaKeywords,
            createdAt: $createdAt,
            updatedAt: $createdAt,
            deletedAt: null,
            category: null,
            user: null,
        );
    }

    public function update(MaterialUpdateData $data): self
    {
        $resolve = fn(mixed $value, mixed $current): mixed => $value instanceof NotSet ? $current : $value;

        $status = $resolve($data->status, $this->status);
        $publishedAt = $this->publishedAt;

        if ($status !== MaterialStatus::PUBLISHED && $this->status === MaterialStatus::PUBLISHED) {
            $publishedAt = null;
        }

        if ($status === MaterialStatus::PUBLISHED && $publishedAt === null) {
            $publishedAt = new \DateTimeImmutable();
        }

        $slug = $resolve($data->slug, $this->slug);
        if ($slug === '') {
            throw new \InvalidArgumentException('Slug cannot be empty');
        }

        return new self(
            id: $this->id,
            title: $resolve($data->title, $this->title),
            slug: $slug,
            content: $resolve($data->content, $this->content),
            categoryId: $resolve($data->categoryId, $this->categoryId),
            userId: $this->userId,
            status: $status,
            access: $resolve($data->access, $this->access),
            views: $this->views,
            publishedAt: $publishedAt,
            featured: $this->featured,
            showOnHomepage: $resolve($data->showOnHomepage, $this->showOnHomepage),
            showDate: $resolve($data->showDate, $this->showDate),
            showAuthor: $resolve($data->showAuthor, $this->showAuthor),
            showCategory: $resolve($data->showCategory, $this->showCategory),
            showViews: $resolve($data->showViews, $this->showViews),
            useGlobalSettings: $resolve($data->useGlobalSettings, $this->useGlobalSettings),
            template: $resolve($data->template, $this->template),
            metaTitle: $resolve($data->metaTitle, $this->metaTitle),
            metaDescription: $resolve($data->metaDescription, $this->metaDescription),
            metaKeywords: $resolve($data->metaKeywords, $this->metaKeywords),
            createdAt: $this->createdAt,
            updatedAt: new \DateTimeImmutable(),
            deletedAt: $this->deletedAt,
            category: $this->category,
            user: $this->user,
        );
    }

    public function publish(\DateTimeImmutable $now): self
    {
        if (!$this->status->canPublish()) {
            throw new \DomainException('Cannot publish material with status: ' . $this->status->value);
        }

        return new self(
            id: $this->id,
            title: $this->title,
            slug: $this->slug,
            content: $this->content,
            categoryId: $this->categoryId,
            userId: $this->userId,
            status: MaterialStatus::PUBLISHED,
            access: $this->access,
            views: $this->views,
            publishedAt: $now,
            featured: $this->featured,
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
            createdAt: $this->createdAt,
            updatedAt: $now,
            deletedAt: $this->deletedAt,
            category: $this->category,
            user: $this->user,
        );
    }

    public function unpublish(\DateTimeImmutable $now): self
    {
        if (!$this->status->canUnpublish()) {
            throw new \DomainException('Cannot unpublish material with status: ' . $this->status->value);
        }

        return new self(
            id: $this->id,
            title: $this->title,
            slug: $this->slug,
            content: $this->content,
            categoryId: $this->categoryId,
            userId: $this->userId,
            status: MaterialStatus::DRAFT,
            access: $this->access,
            views: $this->views,
            publishedAt: null,
            featured: $this->featured,
            showOnHomepage: false,
            showDate: $this->showDate,
            showAuthor: $this->showAuthor,
            showCategory: $this->showCategory,
            showViews: $this->showViews,
            useGlobalSettings: $this->useGlobalSettings,
            template: $this->template,
            metaTitle: $this->metaTitle,
            metaDescription: $this->metaDescription,
            metaKeywords: $this->metaKeywords,
            createdAt: $this->createdAt,
            updatedAt: $now,
            deletedAt: $this->deletedAt,
            category: $this->category,
            user: $this->user,
        );
    }

    public function moveToTrash(\DateTimeImmutable $now): self
    {
        if (!$this->status->canDelete()) {
            throw new \DomainException('Cannot delete material with status: ' . $this->status->value);
        }

        return new self(
            id: $this->id,
            title: $this->title,
            slug: $this->slug,
            content: $this->content,
            categoryId: $this->categoryId,
            userId: $this->userId,
            status: MaterialStatus::TRASH,
            access: $this->access,
            views: $this->views,
            publishedAt: $this->publishedAt,
            featured: $this->featured,
            showOnHomepage: false,
            showDate: $this->showDate,
            showAuthor: $this->showAuthor,
            showCategory: $this->showCategory,
            showViews: $this->showViews,
            useGlobalSettings: $this->useGlobalSettings,
            template: $this->template,
            metaTitle: $this->metaTitle,
            metaDescription: $this->metaDescription,
            metaKeywords: $this->metaKeywords,
            createdAt: $this->createdAt,
            updatedAt: $now,
            deletedAt: $now,
            category: $this->category,
            user: $this->user,
        );
    }

    public function restore(\DateTimeImmutable $now): self
    {
        if (!$this->status->canRestore()) {
            throw new \DomainException('Cannot restore material with status: ' . $this->status->value);
        }

        return new self(
            id: $this->id,
            title: $this->title,
            slug: $this->slug,
            content: $this->content,
            categoryId: $this->categoryId,
            userId: $this->userId,
            status: MaterialStatus::DRAFT,
            access: $this->access,
            views: $this->views,
            publishedAt: $this->publishedAt,
            featured: $this->featured,
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
            createdAt: $this->createdAt,
            updatedAt: $now,
            deletedAt: null,
            category: $this->category,
            user: $this->user,
        );
    }

    public function toggleHomepage(bool $show, \DateTimeImmutable $now): self
    {
        return new self(
            id: $this->id,
            title: $this->title,
            slug: $this->slug,
            content: $this->content,
            categoryId: $this->categoryId,
            userId: $this->userId,
            status: $this->status,
            access: $this->access,
            views: $this->views,
            publishedAt: $this->publishedAt,
            featured: $this->featured,
            showOnHomepage: $show,
            showDate: $this->showDate,
            showAuthor: $this->showAuthor,
            showCategory: $this->showCategory,
            showViews: $this->showViews,
            useGlobalSettings: $this->useGlobalSettings,
            template: $this->template,
            metaTitle: $this->metaTitle,
            metaDescription: $this->metaDescription,
            metaKeywords: $this->metaKeywords,
            createdAt: $this->createdAt,
            updatedAt: $now,
            deletedAt: $this->deletedAt,
            category: $this->category,
            user: $this->user,
        );
    }
}
