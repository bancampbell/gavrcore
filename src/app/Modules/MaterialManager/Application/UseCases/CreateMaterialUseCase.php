<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\DTO\CreateMaterialData;
use App\Modules\MaterialManager\Application\Services\HomepageManager;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Events\MaterialCreated;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\ClockInterface;
use App\Modules\MaterialManager\Domain\Services\SlugGeneratorInterface;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final readonly class CreateMaterialUseCase
{
    public function __construct(
        private MaterialRepositoryInterface $repository,
        private SlugGeneratorInterface $slugGenerator,
        private ClockInterface $clock,
        private HomepageManager $homepageManager,
    ) {}

    public function execute(CreateMaterialData $data, int $actorId): Material
    {
        $slug = ! empty($data->slug) ? $data->slug : $this->slugGenerator->generate($data->title);
        if (empty($slug)) {
            $slug = 'material-'.bin2hex(random_bytes(4));
        }

        $lock = $this->acquireHomepageLock();
        if ($lock !== null && ! $lock->get()) {
            throw new \DomainException('Не удалось получить блокировку главной страницы');
        }
        try {
            return DB::transaction(function () use ($data, $actorId, $slug) {
                if ($this->repository->existsBySlugWithLock($slug, null, $data->categoryId)) {
                    throw new \DomainException($this->duplicateSlugMessage($data->categoryId));
                }

                $now = $this->clock->now();

                $material = Material::create(
                    title: $data->title,
                    userId: $data->userId,
                    slug: $slug,
                    categoryId: $data->categoryId,
                    content: $data->content,
                    status: $data->status,
                    access: $data->access,
                    showOnHomepage: $data->showOnHomepage,
                    showDate: $data->showDate,
                    showAuthor: $data->showAuthor,
                    showCategory: $data->showCategory,
                    showViews: $data->showViews,
                    useGlobalSettings: $data->useGlobalSettings,
                    metaTitle: $data->metaTitle,
                    metaDescription: $data->metaDescription,
                    metaKeywords: $data->metaKeywords,
                    createdAt: $now,
                );

                try {
                    $saved = $this->repository->save($material);
                } catch (UniqueConstraintViolationException) {
                    // Гонка созданий: оба запроса прошли валидацию и
                    // exists-check, проигравший на частичном unique-индексе
                    // (category_id, slug) получает 422, а не 500 (детект
                    // unique по эталону UserManager, п.5).
                    throw new \DomainException($this->duplicateSlugMessage($data->categoryId));
                }

                if ($data->showOnHomepage) {
                    $this->homepageManager->assign($saved->id);
                }

                DB::afterCommit(fn () => Event::dispatch(new MaterialCreated($saved, $actorId)));

                return $saved;
            });
        } finally {
            $lock?->release();
        }
    }

    private function duplicateSlugMessage(?int $categoryId): string
    {
        return $categoryId !== null
            ? 'В этой категории уже есть материал с таким адресом'
            : 'Материал с таким адресом уже существует';
    }

    /**
     * Драйверы кэша file/array не поддерживают locking: Cache::lock()
     * бросает BadMethodCallException → 500. Как в MediaRepository::acquireLock:
     * без блокировки операция не падает, гонка homepage остаётся возможной,
     * но доменная инвариантность (одна главная) восстанавливается assign'ом
     * последней записи — приемлемый best-effort для не-locking драйверов.
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
