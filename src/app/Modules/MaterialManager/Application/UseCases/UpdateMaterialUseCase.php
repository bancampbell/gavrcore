<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\DTO\UpdateMaterialData;
use App\Modules\MaterialManager\Application\Services\HomepageManager;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Events\MaterialUpdated;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\ClockInterface;
use App\Modules\MaterialManager\Domain\Services\SlugGeneratorInterface;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialStatus;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialUpdateData;
use App\Modules\MaterialManager\Domain\ValueObjects\NotSet;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final readonly class UpdateMaterialUseCase
{
    public function __construct(
        private MaterialRepositoryInterface $repository,
        private SlugGeneratorInterface $slugGenerator,
        private ClockInterface $clock,
        private HomepageManager $homepageManager,
    ) {}

    public function execute(int $id, UpdateMaterialData $data, int $actorId): Material
    {
        $lock = $this->acquireHomepageLock();
        if ($lock !== null && ! $lock->get()) {
            throw new \DomainException('Не удалось получить блокировку главной страницы');
        }
        try {
            return DB::transaction(function () use ($id, $data, $actorId) {
                $material = $this->repository->findByIdWithLock($id);

                if (! $material) {
                    // 404 вместо 500 (см. GetMaterialForEditUseCase)
                    throw (new ModelNotFoundException())->setModel(Material::class, [$id]);
                }

                $domainData = $data->toDomain();

                $newSlug = null;

                if (! empty($domainData->slug) && ! ($domainData->slug instanceof NotSet)) {
                    $newSlug = $domainData->slug;
                } elseif ($domainData->title !== null && ! ($domainData->title instanceof NotSet)) {
                    $generated = $this->slugGenerator->generate($domainData->title);
                    // Пустая генерация (например, регистр/символы вне таблицы
                    // транслитерации) — slug не меняем вместо падения с
                    // «Slug cannot be empty».
                    $newSlug = $generated !== '' ? $generated : $material->slug;
                }

                if ($newSlug !== null && $newSlug !== $material->slug) {
                    if ($newSlug === '') {
                        throw new \DomainException('Slug cannot be empty');
                    }
                    // Уникальность slug — в рамках новой категории (если
                    // передана) либо текущей; для безкатегорийных — среди
                    // прочих безкатегорийных (category_id IS NULL).
                    $scopeCategoryId = $domainData->categoryId instanceof NotSet
                        ? $material->categoryId
                        : $domainData->categoryId;
                    if ($this->repository->existsBySlugWithLock($newSlug, $material->id, $scopeCategoryId)) {
                        throw new \DomainException(
                            $scopeCategoryId !== null
                                ? 'В этой категории уже есть материал с таким адресом'
                                : 'Материал с таким адресом уже существует'
                        );
                    }
                }

                if ($newSlug !== null) {
                    $domainData = new MaterialUpdateData(
                        title: $domainData->title,
                        slug: $newSlug,
                        content: $domainData->content,
                        categoryId: $domainData->categoryId,
                        status: $domainData->status,
                        access: $domainData->access,
                        showOnHomepage: $domainData->showOnHomepage,
                        showDate: $domainData->showDate,
                        showAuthor: $domainData->showAuthor,
                        showCategory: $domainData->showCategory,
                        showViews: $domainData->showViews,
                        useGlobalSettings: $domainData->useGlobalSettings,
                        template: $domainData->template,
                        metaTitle: $domainData->metaTitle,
                        metaDescription: $domainData->metaDescription,
                        metaKeywords: $domainData->metaKeywords,
                    );
                }

                $updated = $material->update($domainData);

                if ($updated->status === MaterialStatus::PUBLISHED && $updated->publishedAt === null) {
                    $now = $this->clock->now();
                    $updated = new Material(
                        id: $updated->id,
                        title: $updated->title,
                        slug: $updated->slug,
                        content: $updated->content,
                        categoryId: $updated->categoryId,
                        userId: $updated->userId,
                        status: $updated->status,
                        access: $updated->access,
                        views: $updated->views,
                        publishedAt: $now,
                        featured: $updated->featured,
                        showOnHomepage: $updated->showOnHomepage,
                        showDate: $updated->showDate,
                        showAuthor: $updated->showAuthor,
                        showCategory: $updated->showCategory,
                        showViews: $updated->showViews,
                        useGlobalSettings: $updated->useGlobalSettings,
                        template: $updated->template,
                        metaTitle: $updated->metaTitle,
                        metaDescription: $updated->metaDescription,
                        metaKeywords: $updated->metaKeywords,
                        createdAt: $updated->createdAt,
                        updatedAt: $now,
                        deletedAt: $updated->deletedAt,
                        category: $updated->category,
                        user: $updated->user,
                    );
                }

                try {
                    $saved = $this->repository->save($updated);
                } catch (UniqueConstraintViolationException) {
                    // Переезд в категорию со сменой slug без валидационной
                    // проверки (category_id пришёл вместе со slug, гонка
                    // правок): конфликт частичного unique-индекса
                    // (category_id, slug) — 422, а не 500 (детект unique
                    // по эталону UserManager, п.5).
                    $scopeCategoryId = $domainData->categoryId instanceof NotSet
                        ? $material->categoryId
                        : $domainData->categoryId;

                    throw new \DomainException(
                        $scopeCategoryId !== null
                            ? 'В этой категории уже есть материал с таким адресом'
                            : 'Материал с таким адресом уже существует'
                    );
                }

                if ($data->showOnHomepage === true) {
                    $this->homepageManager->assign($saved->id);
                } elseif ($data->showOnHomepage === false) {
                    $this->homepageManager->unassign($saved->id);
                }

                DB::afterCommit(fn () => Event::dispatch(new MaterialUpdated($saved, $actorId)));

                return $saved;
            });
        } finally {
            $lock?->release();
        }
    }

    /**
     * Драйверы кэша file/array не поддерживают locking: Cache::lock()
     * бросает BadMethodCallException → 500 на create/update материала.
     * Как в MediaRepository::acquireLock: операция выполняется без блокировки,
     * но не падает. Блокировка остаётся обязательной там, где драйвер её
     * поддерживает (redis/memcached/database) — race homepage не защищён
     * на file-кэше, что приемлемо: это best-effort страховка от double-assign.
     */
    private function acquireHomepageLock(): ?Lock
    {
        try {
            return Cache::lock('material_manager_homepage', 10);
        } catch (\BadMethodCallException) {
            return null;
        }
    }
}
