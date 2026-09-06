<?php

namespace App\Modules\UserManager\Infrastructure\Repositories;

use App\Modules\UserManager\Application\DTO\CreateGroupData;
use App\Modules\UserManager\Application\DTO\GroupFiltersData;
use App\Modules\UserManager\Application\DTO\UpdateGroupData;
use App\Modules\UserManager\Domain\Entities\Group;
use App\Modules\UserManager\Domain\Repositories\GroupRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\Alias;
use App\Modules\UserManager\Domain\ValueObjects\GroupId;
use App\Modules\UserManager\Infrastructure\Models\GroupModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class GroupRepository implements GroupRepositoryInterface
{
    private const MAX_ALIAS_ATTEMPTS = 50;

    public function paginate(GroupFiltersData $filters): LengthAwarePaginator
    {
        $query = GroupModel::query();

        if (! empty($filters->search)) {
            // LOWER(...) LIKE — переносимая замена Postgres-only ilike.
            $like = '%'.mb_strtolower($filters->search).'%';

            $query->whereRaw('LOWER(name) LIKE ?', [$like]);
        }

        if (! is_null($filters->status)) {
            $query->where('status', $filters->status);
        }

        $paginator = $query->orderBy('ordering')->paginate($filters->perPage);
        $paginator->setCollection(
            $paginator->getCollection()->map(fn (GroupModel $model) => $this->toEntity($model))
        );

        return $paginator;
    }

    public function getAll(): Collection
    {
        return GroupModel::orderBy('name')
            ->get()
            ->map(fn (GroupModel $model) => $this->toEntity($model));
    }

    public function findById(GroupId $id): ?Group
    {
        $model = GroupModel::with('permissions')->find($id->value);

        return $model ? $this->toEntity($model) : null;
    }

    public function create(CreateGroupData $data): Group
    {
        $baseAlias = $this->resolveAlias($data->alias);
        $alias = $baseAlias;
        $attempts = 0;

        while (true) {
            try {
                /** @var GroupModel $model */
                $model = GroupModel::create([
                    'name' => $data->name,
                    'alias' => $alias,
                    'description' => $data->description,
                    'status' => $data->status,
                    'ordering' => $data->ordering,
                ]);

                break;
            } catch (QueryException $e) {
                // Гонка: параллельное создание заняло этот алиас раньше.
                // Идём на следующий свободный суффикс; всё остальное пробрасываем.
                if (! $this->isAliasConflict($e) || ++$attempts >= self::MAX_ALIAS_ATTEMPTS) {
                    throw $e;
                }

                $alias = $baseAlias.'-'.$attempts;
            }
        }

        if (! empty($data->permissionIds)) {
            $model->permissions()->sync($data->permissionIds);
        }

        return $this->toEntity($model->fresh('permissions'));
    }

    public function update(GroupId $id, UpdateGroupData $data): Group
    {
        $model = GroupModel::findOrFail($id->value);
        $alias = $this->resolveAlias($data->alias);

        try {
            $model->update([
                'name' => $data->name,
                'alias' => $alias,
                'description' => $data->description,
                'status' => $data->status,
                'ordering' => $data->ordering,
            ]);
        } catch (QueryException $e) {
            // Конфликт алиасов при параллельном редактировании — это
            // ошибка валидации, а не 500.
            if ($this->isAliasConflict($e)) {
                throw ValidationException::withMessages([
                    'alias' => ['Группа с таким алиасом уже существует'],
                ]);
            }

            throw $e;
        }

        if ($data->permissionIds->isPresent()) {
            $model->permissions()->sync($data->permissionIds->value());
        }

        return $this->toEntity($model->fresh('permissions'));
    }

    public function delete(GroupId $id): bool
    {
        return GroupModel::destroy($id->value) > 0;
    }

    public function updateStatus(GroupId $id, bool $status): bool
    {
        return GroupModel::where('id', $id->value)->update(['status' => $status]) > 0;
    }

    /**
     * Алиас обязан быть непустым: Str::slug() не транслитерирует кириллицу,
     * поэтому для кириллического названия без явного алиаса генерация в DTO
     * даёт пустую строку — такое сохранять нельзя (пустые алиасы ломают
     * unique-индекс и isSystem()).
     */
    private function resolveAlias(string $alias): string
    {
        $alias = trim($alias);

        if ($alias === '') {
            throw ValidationException::withMessages([
                'alias' => ['Не удалось сгенерировать алиас — укажите его вручную'],
            ]);
        }

        return $alias;
    }

    /**
     * Конфликт по уникальному индексу alias.
     */
    private function isAliasConflict(QueryException $e): bool
    {
        // MySQL: 1062, PostgreSQL: 23505, SQLite: 8/19/1555/2067 (SQLITE_CONSTRAINT*)
        $code = (string) ($e->errorInfo[1] ?? $e->getCode());

        if (! in_array($code, ['1062', '23505', '8', '19', '1555', '2067'], true)) {
            return false;
        }

        // Проверка, что конфликт именно по alias, а не по другому ограничению.
        return str_contains(strtolower($e->getMessage()), 'alias');
    }

    private function toEntity(GroupModel $model): Group
    {
        return new Group(
            id: GroupId::fromInt($model->id),
            name: $model->name,
            alias: Alias::fromString($model->alias),
            description: $model->description,
            status: (bool) $model->status,
            ordering: (int) $model->ordering,
            permissionIds: $model->relationLoaded('permissions')
                ? $model->permissions->pluck('id')->map(fn ($id) => (int) $id)->all()
                : [],
            createdAt: $model->created_at?->toDateTimeString(),
        );
    }
}
