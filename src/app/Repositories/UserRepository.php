<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\DTO\UserData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = User::with('groups');

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'ilike', '%'.$filters['search'].'%')
                    ->orWhere('username', 'ilike', '%'.$filters['search'].'%')
                    ->orWhere('email', 'ilike', '%'.$filters['search'].'%');
            });
        }

        if (isset($filters['blocked'])) {
            $query->where('blocked', $filters['blocked']);
        }

        if (isset($filters['activated'])) {
            $query->where('activated', $filters['activated']);
        }

        return $query->orderBy('id', 'desc')->paginate(20);
    }

    public function find(int $id): ?User
    {
        return User::with('groups')->find($id);
    }

    public function create(UserData $data): User
    {
        $user = User::create($data->toArray());

        if (! empty($data->groups)) {
            $user->groups()->sync($data->groups);
        }

        return $user->fresh('groups');
    }

    public function update(User $user, UserData $data): User
    {
        $user->update($data->toArray());

        if (isset($data->groups)) {
            $user->groups()->sync($data->groups);
        }

        return $user->fresh('groups');
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function updateStatus(int $id, bool $blocked): bool
    {
        return User::where('id', $id)->update(['blocked' => $blocked]) > 0;
    }
}
