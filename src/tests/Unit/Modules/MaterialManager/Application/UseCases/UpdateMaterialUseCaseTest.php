<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\DTO\UpdateMaterialData;
use App\Modules\MaterialManager\Application\UseCases\UpdateMaterialUseCase;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\ClockInterface;
use App\Modules\MaterialManager\Domain\Services\SlugGeneratorInterface;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialAccess;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialStatus;
use App\Modules\MaterialManager\Domain\ValueObjects\NotSet;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class UpdateMaterialUseCaseTest extends TestCase
{
    private MaterialRepositoryInterface&MockObject $repository;
    private SlugGeneratorInterface&MockObject $slugGenerator;
    private ClockInterface&MockObject $clock;
    private UpdateMaterialUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();

        $this->repository = $this->createMock(MaterialRepositoryInterface::class);
        $this->slugGenerator = $this->createMock(SlugGeneratorInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->clock->method('now')->willReturn(new \DateTimeImmutable('2026-01-01 12:00:00'));

        $this->useCase = new UpdateMaterialUseCase(
            $this->repository,
            $this->slugGenerator,
            $this->clock,
        );
    }

    private function withId(Material $material, int $id): Material
    {
        $serialized = serialize($material);
        $serialized = preg_replace('/"id";N;/', '"id";i:' . $id . ';', $serialized);
        return unserialize($serialized);
    }

    public function test_updates_title_and_slug(): void
    {
        $material = Material::create(
            title: 'Old',
            userId: 1,
            slug: 'old-slug',
        );

        $data = new UpdateMaterialData(
            title: 'New',
            slug: 'new-slug',
            content: NotSet::instance(),
            categoryId: NotSet::instance(),
            status: NotSet::instance(),
            access: NotSet::instance(),
            showOnHomepage: NotSet::instance(),
            showDate: NotSet::instance(),
            showAuthor: NotSet::instance(),
            showCategory: NotSet::instance(),
            showViews: NotSet::instance(),
            useGlobalSettings: NotSet::instance(),
            template: NotSet::instance(),
            metaTitle: NotSet::instance(),
            metaDescription: NotSet::instance(),
            metaKeywords: NotSet::instance(),
        );

        $this->repository->expects($this->once())
            ->method('findByIdWithLock')
            ->with(1)
            ->willReturn($material);

        $this->repository->expects($this->once())
            ->method('existsBySlugWithLock')
            ->with('new-slug', null)
            ->willReturn(false);

        $this->repository->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn ($m) => $this->withId($m, 1));

        $result = $this->useCase->execute(1, $data, 99);

        $this->assertSame('New', $result->title);
        $this->assertSame('new-slug', $result->slug);
    }

    public function test_generates_slug_from_title_when_slug_not_provided(): void
    {
        $material = Material::create(
            title: 'Old',
            userId: 1,
            slug: 'old-slug',
        );

        $data = new UpdateMaterialData(
            title: 'New Title',
            slug: NotSet::instance(),
            content: NotSet::instance(),
            categoryId: NotSet::instance(),
            status: NotSet::instance(),
            access: NotSet::instance(),
            showOnHomepage: NotSet::instance(),
            showDate: NotSet::instance(),
            showAuthor: NotSet::instance(),
            showCategory: NotSet::instance(),
            showViews: NotSet::instance(),
            useGlobalSettings: NotSet::instance(),
            template: NotSet::instance(),
            metaTitle: NotSet::instance(),
            metaDescription: NotSet::instance(),
            metaKeywords: NotSet::instance(),
        );

        $this->repository->method('findByIdWithLock')->willReturn($material);
        $this->repository->method('existsBySlugWithLock')->willReturn(false);
        $this->slugGenerator->expects($this->once())
            ->method('generate')
            ->with('New Title')
            ->willReturn('new-title');

        $this->repository->method('save')->willReturnCallback(fn ($m) => $this->withId($m, 1));

        $result = $this->useCase->execute(1, $data, 99);

        $this->assertSame('new-title', $result->slug);
    }

    public function test_throws_when_new_slug_already_exists(): void
    {
        $material = Material::create(
            title: 'Old',
            userId: 1,
            slug: 'old-slug',
        );

        $data = new UpdateMaterialData(
            title: NotSet::instance(),
            slug: 'taken',
            content: NotSet::instance(),
            categoryId: NotSet::instance(),
            status: NotSet::instance(),
            access: NotSet::instance(),
            showOnHomepage: NotSet::instance(),
            showDate: NotSet::instance(),
            showAuthor: NotSet::instance(),
            showCategory: NotSet::instance(),
            showViews: NotSet::instance(),
            useGlobalSettings: NotSet::instance(),
            template: NotSet::instance(),
            metaTitle: NotSet::instance(),
            metaDescription: NotSet::instance(),
            metaKeywords: NotSet::instance(),
        );

        $this->repository->method('findByIdWithLock')->willReturn($material);
        $this->repository->method('existsBySlugWithLock')->willReturn(true);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Материал с таким слагом уже существует');

        $this->useCase->execute(1, $data, 99);
    }

    public function test_sets_published_at_when_publishing(): void
    {
        $material = Material::create(
            title: 'Draft',
            userId: 1,
            slug: 'draft',
            status: MaterialStatus::DRAFT,
        );

        $data = new UpdateMaterialData(
            title: NotSet::instance(),
            slug: NotSet::instance(),
            content: NotSet::instance(),
            categoryId: NotSet::instance(),
            status: MaterialStatus::PUBLISHED,
            access: NotSet::instance(),
            showOnHomepage: NotSet::instance(),
            showDate: NotSet::instance(),
            showAuthor: NotSet::instance(),
            showCategory: NotSet::instance(),
            showViews: NotSet::instance(),
            useGlobalSettings: NotSet::instance(),
            template: NotSet::instance(),
            metaTitle: NotSet::instance(),
            metaDescription: NotSet::instance(),
            metaKeywords: NotSet::instance(),
        );

        $this->repository->method('findByIdWithLock')->willReturn($material);
        $this->repository->method('save')->willReturnCallback(fn ($m) => $this->withId($m, 1));

        $result = $this->useCase->execute(1, $data, 99);

        $this->assertSame(MaterialStatus::PUBLISHED, $result->status);
        $this->assertNotNull($result->publishedAt);
    }

    public function test_clears_homepage_when_show_on_homepage_true(): void
    {
        $material = Material::create(
            title: 'Test',
            userId: 1,
            slug: 'test',
        );

        $data = new UpdateMaterialData(
            title: NotSet::instance(),
            slug: NotSet::instance(),
            content: NotSet::instance(),
            categoryId: NotSet::instance(),
            status: NotSet::instance(),
            access: NotSet::instance(),
            showOnHomepage: true,
            showDate: NotSet::instance(),
            showAuthor: NotSet::instance(),
            showCategory: NotSet::instance(),
            showViews: NotSet::instance(),
            useGlobalSettings: NotSet::instance(),
            template: NotSet::instance(),
            metaTitle: NotSet::instance(),
            metaDescription: NotSet::instance(),
            metaKeywords: NotSet::instance(),
        );

        $this->repository->method('findByIdWithLock')->willReturn($material);
        $this->repository->method('save')->willReturnCallback(fn ($m) => $this->withId($m, 1));
        $this->repository->expects($this->once())->method('clearHomepageExcept')->with(1);

        $this->useCase->execute(1, $data, 99);
    }
}
