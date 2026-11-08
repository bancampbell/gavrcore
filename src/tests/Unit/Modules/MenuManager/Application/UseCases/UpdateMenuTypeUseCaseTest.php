<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Application\DTO\UpdateMenuTypeData;
use App\Modules\MenuManager\Application\UseCases\UpdateMenuTypeUseCase;
use App\Modules\MenuManager\Domain\Repositories\MenuTypeRepositoryInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Конфликт уникального alias при update (TECHDEBT п.2б): гонка между
 * валидацией Rule::unique и UPDATE — это 422, а не 500. Детект по SQLSTATE
 * из errorInfo[0]/getCode() (эталон — UserManager).
 */
class UpdateMenuTypeUseCaseTest extends TestCase
{
    private MenuTypeRepositoryInterface&MockObject $repository;

    private UpdateMenuTypeUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(MenuTypeRepositoryInterface::class);
        $this->useCase = new UpdateMenuTypeUseCase($this->repository);
    }

    public function test_concurrent_alias_conflict_becomes_validation_error(): void
    {
        $this->repository->method('findById')->willReturn($this->existingType());
        $this->repository->method('update')->willThrowException($this->pgUniqueViolation());

        try {
            $this->useCase->execute(1, $this->data(alias: 'main-menu'));
            $this->fail('Ожидался ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('alias', $e->errors());
            $this->assertSame(
                ['Тип меню с таким алиасом уже существует'],
                $e->errors()['alias']
            );
        }
    }

    public function test_non_unique_query_exception_propagates(): void
    {
        $this->repository->method('findById')->willReturn($this->existingType());

        $exception = $this->pgQueryException('23503', 'foreign key violation');
        $this->repository->method('update')->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $this->useCase->execute(1, $this->data(alias: 'main-menu'));
    }

    public function test_update_passes_through_when_no_conflict(): void
    {
        $existing = $this->existingType();
        $this->repository->method('findById')->willReturn($existing);
        $this->repository->expects($this->once())
            ->method('update')
            ->with(1, $this->callback(fn (array $data) => $data['alias'] === 'footer'))
            ->willReturn($existing);

        $result = $this->useCase->execute(1, $this->data(alias: 'footer'));

        $this->assertSame($existing, $result);
    }

    private function existingType(): MenuTypeModel
    {
        return new MenuTypeModel([
            'title' => 'Главное меню',
            'alias' => 'main-menu',
            'ordering' => 0,
            'status' => true,
        ]);
    }

    private function data(?string $alias): UpdateMenuTypeData
    {
        return new UpdateMenuTypeData(
            title: 'Главное меню',
            alias: $alias,
            description: null,
            ordering: 0,
            status: true,
        );
    }

    /**
     * QueryException в формате pgsql: errorInfo[0] — SQLSTATE 23505,
     * errorInfo[1] — драйверный код (7), а не SQLSTATE.
     */
    private function pgUniqueViolation(): QueryException
    {
        return $this->pgQueryException('23505', 'duplicate key value violates unique constraint "menu_types_alias_unique"');
    }

    private function pgQueryException(string $sqlstate, string $message): QueryException
    {
        $pdo = new \PDOException($message);
        $pdo->errorInfo = [$sqlstate, 7, $message];

        return new QueryException('pgsql', 'update menu_types set alias = ? where id = ?', ['main-menu', 1], $pdo);
    }
}
