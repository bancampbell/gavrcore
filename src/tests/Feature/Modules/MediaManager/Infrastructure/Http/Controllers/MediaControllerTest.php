<?php

namespace Tests\Feature\Modules\MediaManager\Infrastructure\Http\Controllers;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Modules\MediaManager\Application\UseCases\GetContentsUseCase;
use Modules\MediaManager\Application\UseCases\GetFoldersUseCase;
use Modules\MediaManager\Application\UseCases\CreateFolderUseCase;
use Modules\MediaManager\Application\UseCases\RenameItemUseCase;
use Modules\MediaManager\Application\UseCases\DeleteItemUseCase;
use Modules\MediaManager\Application\UseCases\DeleteItemsUseCase;
use Modules\MediaManager\Application\UseCases\CopyItemUseCase;
use Modules\MediaManager\Application\UseCases\UploadFileUseCase;
use Modules\MediaManager\Application\UseCases\GetPaginatedContentsUseCase;
use Modules\MediaManager\Application\DTO\PaginatedContentsData;

class MediaControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $user = $this->createMock(\App\Models\User::class);
        $this->actingAs($user);

        Gate::define('manage-media', function () {
            return true;
        });

        $this->withoutMiddleware(\App\Http\Middleware\ShareMenuMiddleware::class);
    }

    public function test_index_returns_inertia_page(): void
    {
        $response = $this->get('/admin/media');
        $response->assertStatus(200);
    }

    public function test_get_contents_returns_json(): void
    {
        $useCase = $this->mock(GetContentsUseCase::class);
        $useCase->shouldReceive('execute')
            ->with('test-path')
            ->once()
            ->andReturn([]);

        $response = $this->get('/admin/media/contents?path=test-path');

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_get_contents_returns_json_with_data(): void
    {
        $mockData = [
            ['name' => 'folder1', 'path' => 'folder1', 'type' => 'folder'],
            ['name' => 'file.txt', 'path' => 'file.txt', 'type' => 'file'],
        ];

        $useCase = $this->mock(GetContentsUseCase::class);
        $useCase->shouldReceive('execute')
            ->with('')
            ->once()
            ->andReturn($mockData);

        $response = $this->get('/admin/media/contents');

        $response->assertStatus(200);
        $response->assertJson($mockData);
    }

    public function test_get_paginated_contents_returns_json(): void
    {
        $useCase = $this->mock(GetPaginatedContentsUseCase::class);
        $useCase->shouldReceive('execute')
            ->once()
            ->with(\Mockery::on(function ($arg) {
                return $arg instanceof PaginatedContentsData
                    && $arg->path === 'test-path'
                    && $arg->page === 1
                    && $arg->perPage === 20
                    && $arg->sort === 'name_asc'
                    && $arg->search === null;
            }))
            ->andReturn([
                'data' => [],
                'total' => 0,
                'page' => 1,
                'per_page' => 20,
                'last_page' => 1,
            ]);

        $response = $this->get('/admin/media/contents/paginated?path=test-path&page=1&per_page=20&sort=name_asc');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'total',
            'page',
            'per_page',
            'last_page',
        ]);
    }

    public function test_get_paginated_contents_with_search(): void
    {
        $useCase = $this->mock(GetPaginatedContentsUseCase::class);
        $useCase->shouldReceive('execute')
            ->once()
            ->with(\Mockery::on(function ($arg) {
                return $arg instanceof PaginatedContentsData
                    && $arg->search === 'document';
            }))
            ->andReturn([
                'data' => [],
                'total' => 0,
                'page' => 1,
                'per_page' => 20,
                'last_page' => 1,
            ]);

        $response = $this->get('/admin/media/contents/paginated?path=&page=1&per_page=20&sort=name_asc&search=document');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'total',
            'page',
            'per_page',
            'last_page',
        ]);
    }

    public function test_get_folders_returns_json(): void
    {
        $mockData = [
            ['name' => '2025', 'path' => '2025', 'type' => 'folder'],
            ['name' => '2026', 'path' => '2026', 'type' => 'folder'],
        ];

        $useCase = $this->mock(GetFoldersUseCase::class);
        $useCase->shouldReceive('execute')
            ->once()
            ->andReturn($mockData);

        $response = $this->get('/admin/media/folders');

        $response->assertStatus(200);
        $response->assertJson($mockData);
    }

    public function test_create_folder_returns_json_success(): void
    {
        $useCase = $this->mock(CreateFolderUseCase::class);
        $useCase->shouldReceive('execute')
            ->once();

        $response = $this->postJson('/admin/media/folder', [
            'name' => 'new-folder',
            'path' => '',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'message' => 'Папка создана']);
    }

    public function test_rename_item_returns_json_success(): void
    {
        $useCase = $this->mock(RenameItemUseCase::class);
        $useCase->shouldReceive('execute')
            ->once();

        $response = $this->postJson('/admin/media/rename', [
            'old_path' => 'old-folder',
            'new_name' => 'new-folder',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'message' => 'Переименовано успешно']);
    }

    public function test_delete_item_returns_json_success(): void
    {
        $useCase = $this->mock(DeleteItemUseCase::class);
        $useCase->shouldReceive('execute')
            ->once();

        $response = $this->deleteJson('/admin/media/item', [
            'path' => 'test-folder',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'message' => 'Удалено успешно']);
    }

    public function test_delete_multiple_returns_json_success(): void
    {
        $useCase = $this->mock(DeleteItemsUseCase::class);
        $useCase->shouldReceive('execute')
            ->once()
            ->andReturn(new \Modules\MediaManager\Application\DTO\OperationResult(
                success: true,
                message: 'Удалено элементов: 2',
                data: ['deleted' => ['folder1', 'folder2']],
            ));

        $response = $this->deleteJson('/admin/media/items', [
            'paths' => ['folder1', 'folder2'],
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Удалено элементов: 2',
            'data' => ['deleted' => ['folder1', 'folder2']],
        ]);
    }

    public function test_copy_item_returns_json_success(): void
    {
        $useCase = $this->mock(CopyItemUseCase::class);
        $useCase->shouldReceive('execute')
            ->once();

        $response = $this->postJson('/admin/media/copy', [
            'path' => 'test-file.txt',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'message' => 'Скопировано успешно']);
    }

    public function test_upload_file_returns_json_success(): void
    {
        $useCase = $this->mock(UploadFileUseCase::class);
        $useCase->shouldReceive('execute')
            ->once()
            ->andReturn(new \Modules\MediaManager\Application\DTO\OperationResult(
                success: true,
                message: 'Загружено файлов: 2',
                data: ['uploaded' => ['file1.jpg', 'file2.png']],
            ));

        $response = $this->postJson('/admin/media/upload', [
            'files' => [
                UploadedFile::fake()->create('file1.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('file2.png', 100, 'image/png'),
            ],
            'path' => '',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Загружено файлов: 2',
            'data' => ['uploaded' => ['file1.jpg', 'file2.png']],
        ]);
    }

    public function test_create_folder_validation_fails(): void
    {
        $response = $this->postJson('/admin/media/folder', [
            'name' => '',
            'path' => '',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('name');
    }

    public function test_rename_item_validation_fails(): void
    {
        $response = $this->postJson('/admin/media/rename', [
            'old_path' => '',
            'new_name' => '',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['old_path', 'new_name']);
    }

    public function test_delete_item_validation_fails(): void
    {
        $response = $this->deleteJson('/admin/media/item', [
            'path' => '',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('path');
    }

    public function test_delete_multiple_validation_fails(): void
    {
        $response = $this->deleteJson('/admin/media/items', [
            'paths' => [],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('paths');
    }

    public function test_non_admin_gets_403(): void
    {
        Gate::define('manage-media', function () {
            return false;
        });

        $response = $this->get('/admin/media');

        $response->assertStatus(403);
    }
}
