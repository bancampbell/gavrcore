<?php

namespace App\Modules\UserManager\Infrastructure\Repositories;

use App\Modules\UserManager\Application\DTO\CreateUserData;
use App\Modules\UserManager\Application\DTO\UpdateUserData;
use App\Modules\UserManager\Application\DTO\UserFiltersData;
use App\Modules\UserManager\Domain\Entities\Group;
use App\Modules\UserManager\Domain\Entities\User;
use App\Modules\UserManager\Domain\Repositories\UserRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\Alias;
use App\Modules\UserManager\Domain\ValueObjects\Email;
use App\Modules\UserManager\Domain\ValueObjects\GroupId;
use App\Modules\UserManager\Domain\ValueObjects\UserId;
use App\Modules\UserManager\Domain\ValueObjects\Username;
use App\Modules\UserManager\Infrastructure\Models\GroupModel;
use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

final class UserRepository implements UserRepositoryInterface
{
    public function paginate(UserFiltersData $filters): LengthAwarePaginator
    {
        $query = UserModel::with('groups');

        if (! empty($filters->search)) {
            // LOWER(...) LIKE — переносимая замена Postgres-only ilike:
            // работает одинаково на PostgreSQL, MySQL и SQLite.
            $like = '%'.mb_strtolower($filters->search).'%';

            $query->where(function ($q) use ($like) {
                $q->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(username) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$like]);
            });
        }

        if (! is_null($filters->blocked)) {
            $query->where('blocked', $filters->blocked);
        }

        if (! is_null($filters->activated)) {
            $query->where('activated', $filters->activated);
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($filters->perPage);
        $paginator->setCollection(
            $paginator->getCollection()->map(fn (UserModel $model) => $this->toEntity($model))
        );

        return $paginator;
    }

    public function findById(UserId $id): ?User
    {
        $model = UserModel::with('groups')->find($id->value);

        return $model ? $this->toEntity($model) : null;
    }

    public function create(CreateUserData $data): User
    {
        /** @var UserModel $model */
        $model = UserModel::create([
            'name' => $data->name,
            'username' => $data->username,
            // Email VO нормализует регистр: единственный канонический вид
            // независимо от точки входа (request, консоль, тест).
            'email' => Email::fromString($data->email)->value,
            'password' => Hash::make($data->password),
            'blocked' => $data->blocked,
            'activated' => $data->activated,
        ]);

        if (! empty($data->groupIds)) {
            $model->groups()->sync($data->groupIds);
        }

        return $this->toEntity($model->fresh('groups'));
    }

    public function update(UserId $id, UpdateUserData $data): User
    {
        $model = UserModel::findOrFail($id->value);

        $payload = [
            'name' => $data->name,
            'username' => $data->username,
            'email' => Email::fromString($data->email)->value,
            'blocked' => $data->blocked,
            'activated' => $data->activated,
        ];

        if ($data->password->isPresent()) {
            $payload['password'] = Hash::make($data->password->value());
        }

        $model->update($payload);

        if ($data->groupIds->isPresent()) {
            $model->groups()->sync($data->groupIds->value());
        }

        return $this->toEntity($model->fresh('groups'));
    }

    public function delete(UserId $id): void
    {
        UserModel::findOrFail($id->value)->delete();
    }

    public function updateStatus(UserId $id, bool $blocked): bool
    {
        return UserModel::where('id', $id->value)->update(['blocked' => $blocked]) > 0;
    }

    private function toEntity(UserModel $model): User
    {
        return new User(
            id: UserId::fromInt($model->id),
            name: $model->name,
            username: Username::fromString($model->username),
            email: Email::fromString($model->email),
            blocked: (bool) $model->blocked,
            activated: (bool) $model->activated,
            groups: $model->relationLoaded('groups')
                ? $model->groups->map(fn (GroupModel $group) => $this->groupToEntity($group))->all()
                : [],
            lastLoginAt: $model->last_login_at?->toDateTimeString(),
            lastLoginIp: $model->last_login_ip,
            createdAt: $model->created_at?->toDateTimeString(),
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
