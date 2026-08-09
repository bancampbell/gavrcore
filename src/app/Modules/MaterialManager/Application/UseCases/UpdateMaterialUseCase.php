<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\DTO\UpdateMaterialData;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Events\MaterialUpdated;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\ClockInterface;
use App\Modules\MaterialManager\Domain\Services\SlugGeneratorInterface;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialStatus;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialUpdateData;
use App\Modules\MaterialManager\Domain\ValueObjects\NotSet;
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
    ) {}

    public function execute(int $id, UpdateMaterialData $data, int $actorId): Material
    {
        $lock = Cache::lock('material_manager_homepage', 10);
        if (!$lock->get()) {
            throw new \DomainException('Не удалось получить блокировку главной страницы');
        }
        try {
            return DB::transaction(function () use ($id, $data, $actorId) {
                $material = $this->repository->findByIdWithLock($id);

                if (!$material) {
                    throw new \DomainException('Material not found');
                }

                $domainData = $data->toDomain();

                $newSlug = null;

                if (!empty($domainData->slug) && !($domainData->slug instanceof NotSet)) {
                    $newSlug = $domainData->slug;
                } elseif ($domainData->title !== null && !($domainData->title instanceof NotSet)) {
                    $newSlug = $this->slugGenerator->generate($domainData->title);
                }

                if ($newSlug !== null && $newSlug !== $material->slug) {
                    if ($newSlug === '') {
                        throw new \DomainException('Slug cannot be empty');
                    }
                    if ($this->repository->existsBySlugWithLock($newSlug, $material->id)) {
                        throw new \DomainException('Материал с таким слагом уже существует');
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

                $saved = $this->repository->save($updated);

                if ($data->showOnHomepage === true) {
                    $this->repository->clearHomepageExcept($saved->id);
                }

                DB::afterCommit(fn () => Event::dispatch(new MaterialUpdated($saved, $actorId)));

                return $saved;
            });
        } catch (UniqueConstraintViolationException $e) {
            throw new \DomainException('Материал с таким слагом уже существует');
        } finally {
            $lock->release();
        }
    }
}
