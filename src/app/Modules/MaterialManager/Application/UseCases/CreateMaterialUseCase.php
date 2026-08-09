<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\DTO\CreateMaterialData;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Events\MaterialCreated;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\ClockInterface;
use App\Modules\MaterialManager\Domain\Services\SlugGeneratorInterface;
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
    ) {}

    public function execute(CreateMaterialData $data, int $actorId): Material
    {
        $baseSlug = !empty($data->slug) ? $data->slug : $this->slugGenerator->generate($data->title);
        if (empty($baseSlug)) {
            $baseSlug = 'material-' . bin2hex(random_bytes(4));
        }
        $attempt = 0;
        $maxAttempts = 50;

        $lock = Cache::lock('material_manager_homepage', 10);
        if (!$lock->get()) {
            throw new \DomainException('Не удалось получить блокировку главной страницы');
        }
        try {
            while ($attempt < $maxAttempts) {
                $attempt++;
                $slug = $attempt === 1 ? $baseSlug : $baseSlug . '-' . $attempt;

                try {
                    return DB::transaction(function () use ($data, $actorId, $slug, $baseSlug) {
                        if ($this->repository->existsBySlugWithLock($slug)) {
                            if ($data->slug !== null) {
                                throw new \DomainException('Материал с таким слагом уже существует');
                            }
                            throw new \RuntimeException('slug_collision');
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

                        $saved = $this->repository->save($material);

                        if ($data->showOnHomepage) {
                            $this->repository->clearHomepageExcept($saved->id);
                        }

                        DB::afterCommit(fn () => Event::dispatch(new MaterialCreated($saved, $actorId)));

                        return $saved;
                    });
                } catch (UniqueConstraintViolationException $e) {
                    if ($data->slug !== null) {
                        throw new \DomainException('Материал с таким слагом уже существует');
                    }
                    if ($attempt >= $maxAttempts) {
                        break;
                    }
                    usleep(random_int(10000, 100000));
                    continue;
                } catch (\RuntimeException $e) {
                    if ($e->getMessage() === 'slug_collision') {
                        if ($attempt >= $maxAttempts) {
                            break;
                        }
                        usleep(random_int(10000, 50000));
                        continue;
                    }
                    throw $e;
                }
            }
        } finally {
            $lock->release();
        }

        throw new \DomainException(
            'Не удалось создать материал: конфликт слага после ' . $maxAttempts . ' попыток'
        );
    }
}
