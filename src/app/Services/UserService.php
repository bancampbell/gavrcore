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
    ) {}

    public function getPaginated(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function find(int $id): ?User
    {
        return $this->repository->find($id);
    }

    public function create(array $data): User
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $userData = UserData::fromArray($data);
        return $this->repository->create($userData);
    }

    public function update(int $id, array $data): User
    {
        $user = $this->repository->find($id);

        if (!empty($data['password'])) {
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
