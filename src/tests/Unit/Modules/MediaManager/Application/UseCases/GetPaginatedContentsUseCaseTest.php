<?php

namespace Tests\Unit\Modules\MediaManager\Application\UseCases;

use Mockery;
use PHPUnit\Framework\TestCase;
use Modules\MediaManager\Application\DTO\PaginatedContentsData;
use Modules\MediaManager\Application\UseCases\GetPaginatedContentsUseCase;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class GetPaginatedContentsUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_execute_returns_paginated_data()
    {
        $mockRepo = Mockery::mock(MediaRepositoryInterface::class);
        $mockRepo->shouldReceive('getPaginatedContents')
            ->once()
            ->with('test/path', 1, 20, 'name_asc', null)
            ->andReturn([
                'data' => [],
                'total' => 0,
                'page' => 1,
                'per_page' => 20,
                'last_page' => 1,
            ]);

        $useCase = new GetPaginatedContentsUseCase($mockRepo);
        $dto = new PaginatedContentsData(
            path: 'test/path',
            page: 1,
            perPage: 20,
            sort: 'name_asc',
            search: null
        );

        $result = $useCase->execute($dto);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('per_page', $result);
        $this->assertArrayHasKey('last_page', $result);
    }

    public function test_execute_with_search_filter()
    {
        $mockRepo = Mockery::mock(MediaRepositoryInterface::class);
        $mockRepo->shouldReceive('getPaginatedContents')
            ->once()
            ->with('test/path', 2, 10, 'name_desc', 'document')
            ->andReturn([
                'data' => [],
                'total' => 0,
                'page' => 2,
                'per_page' => 10,
                'last_page' => 1,
            ]);

        $useCase = new GetPaginatedContentsUseCase($mockRepo);
        $dto = new PaginatedContentsData(
            path: 'test/path',
            page: 2,
            perPage: 10,
            sort: 'name_desc',
            search: 'document'
        );

        $result = $useCase->execute($dto);

        $this->assertEquals(2, $result['page']);
        $this->assertEquals(10, $result['per_page']);
    }
}
