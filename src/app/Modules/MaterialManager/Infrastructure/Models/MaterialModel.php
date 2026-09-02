<?php

namespace App\Modules\MaterialManager\Infrastructure\Models;

use App\Models\User;
use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\ValueObjects\AuthorSummaryVO;
use App\Modules\MaterialManager\Domain\ValueObjects\CategorySummaryVO;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialAccess;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialModel extends Model
{
    use HasFactory;

    protected $table = 'materials';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'category_id',
        'user_id',
        'state',
        'access',
        'views',
        'published_at',
        'featured',
        'show_on_homepage',
        'show_date',
        'show_author',
        'show_category',
        'show_views',
        'use_global_settings',
        'template',
        'alias',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'deleted_at' => 'datetime',
        'views' => 'integer',
        'featured' => 'boolean',
        'show_on_homepage' => 'boolean',
        'show_date' => 'boolean',
        'show_author' => 'boolean',
        'show_category' => 'boolean',
        'show_views' => 'boolean',
        'use_global_settings' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toDomain(): Material
    {
        return new Material(
            id: $this->id,
            title: (string) $this->title,
            slug: (string) $this->slug,
            content: $this->content,
            categoryId: $this->category_id,
            userId: (int) $this->user_id,
            status: MaterialStatus::tryFrom((string) $this->state) ?? MaterialStatus::DRAFT,
            access: MaterialAccess::tryFrom((string) $this->access) ?? MaterialAccess::PUBLIC,
            views: (int) ($this->views ?? 0),
            publishedAt: $this->published_at,
            featured: (bool) $this->featured,
            showOnHomepage: (bool) $this->show_on_homepage,
            showDate: (bool) $this->show_date,
            showAuthor: (bool) $this->show_author,
            showCategory: (bool) $this->show_category,
            showViews: (bool) $this->show_views,
            useGlobalSettings: (bool) $this->use_global_settings,
            template: $this->template,
            metaTitle: $this->meta_title,
            metaDescription: $this->meta_description,
            metaKeywords: $this->meta_keywords,
            createdAt: $this->created_at,
            updatedAt: $this->updated_at,
            deletedAt: $this->deleted_at,
            category: $this->relationLoaded('category') && $this->category ? new CategorySummaryVO(
                id: (int) $this->category->id,
                name: (string) $this->category->name,
                slug: (string) $this->category->alias,
            ) : null,
            user: $this->relationLoaded('user') && $this->user ? new AuthorSummaryVO(
                id: (int) $this->user->id,
                name: (string) $this->user->name,
                email: (string) $this->user->email,
            ) : null,
        );
    }

    public static function fromDomain(Material $material, ?self $model = null): self
    {
        if ($model === null) {
            if ($material->id !== null) {
                throw new \InvalidArgumentException('Existing model required when mapping material with ID');
            }

            $model = new self();
        }

        $model->title = $material->title;
        $model->slug = $material->slug;
        $model->content = $material->content;
        $model->category_id = $material->categoryId;
        $model->user_id = $material->userId;
        $model->state = $material->status->value;
        $model->access = $material->access->value;
        $model->views = $material->views;
        $model->published_at = $material->publishedAt instanceof \DateTimeInterface ? $material->publishedAt : null;
        $model->featured = $material->featured;
        $model->show_on_homepage = $material->showOnHomepage;
        $model->show_date = $material->showDate;
        $model->show_author = $material->showAuthor;
        $model->show_category = $material->showCategory;
        $model->show_views = $material->showViews;
        $model->use_global_settings = $material->useGlobalSettings;
        $model->template = $material->template;
        $model->alias = $material->slug;
        $model->meta_title = $material->metaTitle;
        $model->meta_description = $material->metaDescription;
        $model->meta_keywords = $material->metaKeywords;
        $model->deleted_at = $material->deletedAt;

        return $model;
    }

    protected static function newFactory()
    {
        return \Tests\Feature\Modules\MaterialManager\Infrastructure\Models\MaterialModelFactory::new();
    }
}
