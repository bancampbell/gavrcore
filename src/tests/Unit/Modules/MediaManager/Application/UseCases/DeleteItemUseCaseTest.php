<?php

namespace Tests\Unit\Modules\MediaManager\Application\UseCases;

use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Support\Facades\Event;
use Modules\MediaManager\Application\DTO\DeleteItemData;
use Modules\MediaManager\Application\UseCases\DeleteItemUseCase;
use Modules\MediaManager\Domain\Events\MediaDeleted;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;
use Tests\TestCase;

class DeleteItemUseCaseTest extends TestCase
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
        $this->useCase = new DeleteItemUseCase($this->repository);
    }

    public function test_execute_deletes_item_successfully(): void
    {
        $data = new DeleteItemData(path: 'test-folder');

        $this->repository
            ->expects($this->once())
            ->method('exists')
            ->with('test-folder')
            ->willReturn(true);

        $this->repository
            ->expects($this->once())
            ->method('delete')
            ->with('test-folder');

        $this->useCase->execute($data);

        Event::assertDispatched(MediaDeleted::class, fn ($event) => $event->userId === 1 && $event->path === 'test-folder');
    }

    public function test_execute_throws_exception_when_item_not_found(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Файл или папка не найдены');

        $data = new DeleteItemData(path: 'non-existent');

        $this->repository
            ->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        $this->useCase->execute($data);
    }

    public function test_execute_deletes_item_with_path_containing_slashes(): void
    {
        $data = new DeleteItemData(path: 'folder/subfolder/file.txt');

        $this->repository
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->repository
            ->expects($this->once())
            ->method('delete');

        $this->useCase->execute($data);
    }

    public function test_execute_deletes_folder_successfully(): void
    {
        $data = new DeleteItemData(path: 'test-folder');

        $this->repository
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->repository
            ->expects($this->once())
            ->method('delete');

        $this->useCase->execute($data);
    }
}
