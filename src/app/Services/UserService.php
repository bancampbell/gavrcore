<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\DTO\UserData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {
    }

    /**
     * @param  array<string, mixed>  $filters
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function getPaginated(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function find(int $id): ?User
    {
        return $this->repository->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        if (isset($data['password']) && $data['password'] !== '') {
            $data['password'] = Hash::make($data['password']);
        }

        $userData = UserData::fromArray($data);

        return $this->repository->create($userData);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): User
    {
        $user = $this->repository->find($id);

        if (isset($data['password']) && $data['password'] !== '') {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $userData = UserData::fromArray($data);

        return $this->repository->update($user, $userData);
    }

    public function delete(int $id): void
    {
        $user = $this->repository->find($id);
        $this->repository->delete($user);
    }

    public function updateStatus(int $id, bool $blocked): bool
    {
        return $this->repository->updateStatus($id, $blocked);
    }
}
