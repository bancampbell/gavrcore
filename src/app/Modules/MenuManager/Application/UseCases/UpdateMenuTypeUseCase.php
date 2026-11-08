<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Application\DTO\UpdateMenuTypeData;
use App\Modules\MenuManager\Domain\Repositories\MenuTypeRepositoryInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class UpdateMenuTypeUseCase
{
    public function __construct(private MenuTypeRepositoryInterface $repository) {}

    public function execute(int $id, UpdateMenuTypeData $data): MenuTypeModel
    {
        $existing = $this->repository->findById($id);
        if (! $existing) {
            throw new \RuntimeException('Menu type not found');
        }

        $updateData = array_filter([
            'title' => $data->title,
            'alias' => $data->alias ?? $existing->alias,
            'description' => $data->description,
            'ordering' => $data->ordering,
            'status' => $data->status,
        ], fn ($v) => $v !== null);

        try {
            return $this->repository->update($id, $updateData);
        } catch (QueryException $e) {
            // Гонка: alias заняли между валидацией Rule::unique и UPDATE.
            // Retry с суффиксом здесь недопустим — alias задан явно (или
            // сохранён от существующей записи), молчаливая смена сломала бы
            // семантику; по образцу UserManager (GroupRepository::update)
            // конфликт — это ошибка валидации, а не 500.
            if ($this->isUniqueViolation($e)) {
                throw ValidationException::withMessages([
                    'alias' => ['Тип меню с таким алиасом уже существует'],
                ]);
            }

            throw $e;
        }
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        // MySQL: 1062, PostgreSQL: 23505, SQLite: 8/19/1555/2067 (SQLITE_CONSTRAINT*).
        // Драйвер pgsql кладёт в errorInfo[1] свой код (7), а не SQLSTATE —
        // 23505 ищем в errorInfo[0]/getCode().
        $codes = array_filter([
            (string) ($e->errorInfo[0] ?? ''),
            (string) $e->getCode(),
        ]);

        return count(array_intersect($codes, ['1062', '23505', '8', '19', '1555', '2067'])) > 0;
    }
}
