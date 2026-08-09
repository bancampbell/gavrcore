<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\DTO\CreateMaterialData;
use App\Modules\MaterialManager\Application\UseCases\CreateMaterialUseCase;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\ClockInterface;
use App\Modules\MaterialManager\Domain\Services\SlugGeneratorInterface;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialAccess;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialStatus;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class CreateMaterialUseCaseTest extends TestCase
{
    private MaterialRepositoryInterface&MockObject $repository;
    private SlugGeneratorInterface&MockObject $slugGenerator;
    private ClockInterface&MockObject $clock;
    private CreateMaterialUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();

        $this->repository = $this->createMock(MaterialRepositoryInterface::class);
        $this->slugGenerator = $this->createMock(SlugGeneratorInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->clock->method('now')->willReturn(new \DateTimeImmutable('2026-01-01 12:00:00'));

        $this->useCase = new CreateMaterialUseCase(
            $this->repository,
            $this->slugGenerator,
            $this->clock,
        );
    }

    /**
     * Хелпер для установки id на readonly Material через сериализацию.
     */
    private function withId(Material $material, int $id): Material
    {
        $serialized = serialize($material);
        $serialized = preg_replace('/"id";N;/', '"id";i:' . $id . ';', $serialized);
        return unserialize($serialized);
    }

    public function test_creates_material_with_generated_slug(): void
    {
        $data = new CreateMaterialData(
            title: 'Hello World',
            slug: null,
            content: null,
            categoryId: null,
            userId: 1,
            status: MaterialStatus::DRAFT,
            access: MaterialAccess::PUBLIC,
            showOnHomepage: false,
            showDate: true,
            showAuthor: true,
            showCategory: true,
            showViews: true,
            useGlobalSettings: true,
            metaTitle: null,
            metaDescription: null,
            metaKeywords: null,
        );

        $this->slugGenerator->expects($this->once())
            ->method('generate')
            ->with('Hello World')
            ->willReturn('hello-world');

        $this->repository->expects($this->once())
            ->method('existsBySlugWithLock')
            ->with('hello-world')
            ->willReturn(false);

        $this->repository->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn ($m) => $this->withId($m, 1));

        $result = $this->useCase->execute($data, 1);

        $this->assertSame('hello-world', $result->slug);
        $this->assertSame(MaterialStatus::DRAFT, $result->status);
    }

    public function test_throws_when_explicit_slug_already_exists(): void
    {
        $data = new CreateMaterialData(
            title: 'Hello',
            slug: 'taken',
            content: null,
            categoryId: null,
            userId: 1,
            status: MaterialStatus::DRAFT,
            access: MaterialAccess::PUBLIC,
            showOnHomepage: false,
            showDate: true,
            showAuthor: true,
            showCategory: true,
            showViews: true,
            useGlobalSettings: true,
            metaTitle: null,
            metaDescription: null,
            metaKeywords: null,
        );

        $this->repository->expects($this->once())
            ->method('existsBySlugWithLock')
            ->with('taken')
            ->willReturn(true);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Материал с таким слагом уже существует');

        $this->useCase->execute($data, 1);
    }

    public function test_clears_homepage_when_flag_is_set(): void
    {
        $data = new CreateMaterialData(
            title: 'Homepage',
            slug: null,
            content: null,
            categoryId: null,
            userId: 1,
            status: MaterialStatus::DRAFT,
            access: MaterialAccess::PUBLIC,
            showOnHomepage: true,
            showDate: true,
            showAuthor: true,
            showCategory: true,
            showViews: true,
            useGlobalSettings: true,
            metaTitle: null,
            metaDescription: null,
            metaKeywords: null,
        );

        $this->slugGenerator->method('generate')->willReturn('homepage');
        $this->repository->method('existsBySlugWithLock')->willReturn(false);
        $this->repository->expects($this->once())->method('clearHomepageExcept')->with(1);
        $this->repository->method('save')->willReturnCallback(fn ($m) => $this->withId($m, 1));

        $this->useCase->execute($data, 1);
    }
}
