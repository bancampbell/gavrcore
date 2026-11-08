<?php

namespace Tests\Unit\Modules\MediaManager\Application\UseCases;

use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Support\Facades\Event;
use Modules\MediaManager\Application\DTO\CopyItemData;
use Modules\MediaManager\Application\UseCases\CopyItemUseCase;
use Modules\MediaManager\Domain\Events\MediaCopied;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;
use Tests\TestCase;

class CopyItemUseCaseTest extends TestCase
{
    private $repository;

    private $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = new UserModel();
        $user->forceFill(['id' => 1, 'name' => 'Admin', 'email' => 'admin@example.com']);
        $this->actingAs($user);
        Event::fake();

        $this->repository = $this->createMock(MediaRepositoryInterface::class);
        $this->useCase = new CopyItemUseCase($this->repository);
    }

    public function test_execute_copies_item_successfully(): void
    {
        $data = new CopyItemData(path: 'test-file.txt');

        $this->repository
            ->expects($this->once())
            ->method('exists')
            ->with('test-file.txt')
            ->willReturn(true);

        $this->repository
            ->expects($this->once())
            ->method('copy')
            ->with('test-file.txt');

        $this->useCase->execute($data);

        Event::assertDispatched(MediaCopied::class, fn ($event) => $event->userId === 1 && $event->path === 'test-file.txt');
    }

    public function test_execute_throws_exception_when_item_not_found(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Файл или папка не найдены');

        $data = new CopyItemData(path: 'non-existent.txt');

        $this->repository
            ->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $this->useCase->execute($data);
    }

    public function test_execute_copies_folder_successfully(): void
    {
        $data = new CopyItemData(path: 'test-folder');

        $this->repository
            ->expects($this->once())
            ->method('exists')
            ->with('test-folder')
            ->willReturn(true);

        $this->repository
            ->expects($this->once())
            ->method('copy')
            ->with('test-folder');

        $this->useCase->execute($data);
    }
}
