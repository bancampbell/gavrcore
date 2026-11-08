<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\FormBuilder\Application\UseCases;

use App\Modules\FormBuilder\Application\DTO\CreateFormData;
use App\Modules\FormBuilder\Application\UseCases\CreateFormUseCase;
use App\Modules\FormBuilder\Domain\Entities\Form;
use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;
use App\Modules\FormBuilder\Domain\Services\SlugGeneratorInterface;
use App\Modules\FormBuilder\Domain\ValueObjects\FormFieldCollection;
use App\Modules\FormBuilder\Domain\ValueObjects\FormSettings;
use App\Modules\FormBuilder\Domain\ValueObjects\FormStatus;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Retry-ветка на unique-нарушение alias (TECHDEBT п.5): детект по SQLSTATE
 * из errorInfo[0]/getCode() (эталон — UserManager), не по тексту ошибки.
 */
class CreateFormUseCaseTest extends TestCase
{
    private FormRepositoryInterface&MockObject $repository;

    private SlugGeneratorInterface&MockObject $slugGenerator;

    private CreateFormUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(FormRepositoryInterface::class);
        $this->slugGenerator = $this->createMock(SlugGeneratorInterface::class);
        $this->useCase = new CreateFormUseCase($this->repository, $this->slugGenerator);
    }

    public function test_retries_with_suffix_when_alias_taken_between_check_and_insert(): void
    {
        $data = new CreateFormData(
            title: 'Контакты',
            alias: null,
            description: null,
            fields: [],
            settings: [],
            notificationEmails: [],
            status: true,
            isDynamic: false,
        );

        $this->slugGenerator->method('generate')->willReturn('kontakty');
        // makeUniqueAlias: свободен и для базового, и для суффиксованного варианта.
        $this->repository->method('findByAlias')->willReturn(null);

        $savedAliases = [];
        $this->repository->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(function (Form $form) use (&$savedAliases) {
                $savedAliases[] = $form->alias;

                if (count($savedAliases) === 1) {
                    // Гонка: параллельный create занял alias между findByAlias и INSERT.
                    throw $this->pgUniqueViolation();
                }

                return $form;
            });

        $form = $this->useCase->execute($data);

        $this->assertSame('kontakty', $savedAliases[0]);
        $this->assertNotSame('kontakty', $savedAliases[1]);
        $this->assertSame($savedAliases[1], $form->alias);
    }

    public function test_non_unique_query_exception_is_not_retried(): void
    {
        $data = new CreateFormData(
            title: 'Контакты',
            alias: null,
            description: null,
            fields: [],
            settings: [],
            notificationEmails: [],
            status: true,
            isDynamic: false,
        );

        $this->slugGenerator->method('generate')->willReturn('kontakty');
        $this->repository->method('findByAlias')->willReturn(null);
        $this->repository->expects($this->once())
            ->method('save')
            ->willThrowException($this->pgQueryException('23503', 'foreign key violation'));

        $this->expectException(QueryException::class);

        $this->useCase->execute($data);
    }

    public function test_explicit_alias_conflict_is_not_retried(): void
    {
        $data = new CreateFormData(
            title: 'Контакты',
            alias: 'kontakty',
            description: null,
            fields: [],
            settings: [],
            notificationEmails: [],
            status: true,
            isDynamic: false,
        );

        // Явный alias клиента — конфликт это 422/500 валидации, retry невозможен.
        $this->repository->expects($this->never())->method('findByAlias');
        $this->repository->expects($this->once())
            ->method('save')
            ->willThrowException($this->pgUniqueViolation());

        $this->expectException(QueryException::class);

        $this->useCase->execute($data);
    }

    /**
     * QueryException в формате pgsql: errorInfo[0] — SQLSTATE 23505,
     * errorInfo[1] — драйверный код (7), а не SQLSTATE.
     */
    private function pgUniqueViolation(): QueryException
    {
        return $this->pgQueryException('23505', 'duplicate key value violates unique constraint "forms_alias_unique"');
    }

    private function pgQueryException(string $sqlstate, string $message): QueryException
    {
        $pdo = new \PDOException($message);
        $pdo->errorInfo = [$sqlstate, 7, $message];

        return new QueryException('pgsql', 'insert into forms (alias) values (?)', ['kontakty'], $pdo);
    }
}
