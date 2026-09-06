<?php

namespace App\Modules\UserManager\Infrastructure\Repositories;

use App\Modules\UserManager\Application\DTO\AccessLevelFiltersData;
use App\Modules\UserManager\Application\DTO\CreateAccessLevelData;
use App\Modules\UserManager\Application\DTO\UpdateAccessLevelData;
use App\Modules\UserManager\Domain\Entities\AccessLevel;
use App\Modules\UserManager\Domain\Entities\Group;
use App\Modules\UserManager\Domain\Repositories\AccessLevelRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\AccessLevelId;
use App\Modules\UserManager\Domain\ValueObjects\Alias;
use App\Modules\UserManager\Domain\ValueObjects\GroupId;
use App\Modules\UserManager\Infrastructure\Models\AccessLevelModel;
use App\Modules\UserManager\Infrastructure\Models\GroupModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class AccessLevelRepository implements AccessLevelRepositoryInterface
{
    private const MAX_ALIAS_ATTEMPTS = 50;

    public function getAll(AccessLevelFiltersData $filters): Collection
    {
        $query = AccessLevelModel::with('groups');

        if (! empty($filters->search)) {
            // LOWER(...) LIKE — переносимая замена Postgres-only ilike.
            $like = '%'.mb_strtolower($filters->search).'%';

            $query->where(function ($q) use ($like) {
                $q->whereRaw('LOWER(title) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(alias) LIKE ?', [$like]);
            });
        }

        if (! is_null($filters->status)) {
            $query->where('status', $filters->status);
        }

        return $query->orderBy('ordering')
            ->get()
            ->map(fn (AccessLevelModel $model) => $this->toEntity($model));
    }

    public function findById(AccessLevelId $id): ?AccessLevel
    {
        $model = AccessLevelModel::with('groups')->find($id->value);

        return $model ? $this->toEntity($model) : null;
    }

    public function create(CreateAccessLevelData $data): AccessLevel
    {
        $baseAlias = $this->resolveAlias($data->alias);
        $alias = $baseAlias;
        $attempts = 0;

        while (true) {
            try {
                /** @var AccessLevelModel $model */
                $model = AccessLevelModel::create([
                    'title' => $data->title,
                    'alias' => $alias,
                    'description' => $data->description,
                    'status' => $data->status,
                    'ordering' => ((int) AccessLevelModel::max('ordering')) + 1,
                ]);

                break;
            } catch (QueryException $e) {
                // Гонка: параллельное создание заняло этот алиас раньше.
                if (! $this->isAliasConflict($e) || ++$attempts >= self::MAX_ALIAS_ATTEMPTS) {
                    throw $e;
                }

                $alias = $baseAlias.'-'.$attempts;
            }
        }

        if (! empty($data->groupIds)) {
            $model->groups()->sync($data->groupIds);
        }

        return $this->toEntity($model->fresh('groups'));
    }

    public function update(AccessLevelId $id, UpdateAccessLevelData $data): AccessLevel
    {
        $model = AccessLevelModel::findOrFail($id->value);
        $alias = $this->resolveAlias($data->alias);

        try {
            $model->update([
                'title' => $data->title,
                'alias' => $alias,
                'description' => $data->description,
                'status' => $data->status,
            ]);
        } catch (QueryException $e) {
            // Конфликт алиасов при параллельном редактировании — это
            // ошибка валидации, а не 500.
            if ($this->isAliasConflict($e)) {
                throw ValidationException::withMessages([
                    'alias' => ['Уровень доступа с таким алиасом уже существует'],
                ]);
            }

            throw $e;
        }

        if ($data->groupIds->isPresent()) {
            $model->groups()->sync($data->groupIds->value());
        }

        return $this->toEntity($model->fresh('groups'));
    }

    public function delete(AccessLevelId $id): bool
    {
        $model = AccessLevelModel::findOrFail($id->value);
        $model->groups()->detach();

        return (bool) $model->delete();
    }

    public function updateStatus(AccessLevelId $id, bool $status): bool
    {
        return AccessLevelModel::where('id', $id->value)->update(['status' => $status]) > 0;
    }

    public function updateOrdering(array $order): bool
    {
        foreach ($order as $item) {
            AccessLevelModel::where('id', $item['id'])->update(['ordering' => $item['ordering']]);
        }

        return true;
    }

    /**
     * Алиас обязан быть непустым: Str::slug() не транслитерирует кириллицу,
     * поэтому для кириллического названия без явного алиаса генерация в DTO
     * даёт пустую строку — такое сохранять нельзя.
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

    private function toEntity(AccessLevelModel $model): AccessLevel
    {
        return new AccessLevel(
            id: AccessLevelId::fromInt($model->id),
            title: $model->title,
            alias: Alias::fromString($model->alias),
            description: $model->description,
            ordering: (int) $model->ordering,
            status: (bool) $model->status,
            groups: $model->relationLoaded('groups')
                ? $model->groups->map(fn (GroupModel $group) => $this->groupToEntity($group))->all()
                : [],
        );
    }

    private function groupToEntity(GroupModel $model): Group
    {
        return new Group(
            id: GroupId::fromInt($model->id),
            name: $model->name,
            alias: Alias::fromString($model->alias),
            description: $model->description,
            status: (bool) $model->status,
            ordering: (int) $model->ordering,
        );
    }
}
