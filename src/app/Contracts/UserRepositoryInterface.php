<?php

namespace App\Contracts;

use App\DTO\UserData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(array $filters): LengthAwarePaginator;

    public function find(int $id): ?User;
    public function create(UserData $data): User;
    public function update(User $user, UserData $data): User;
    public function delete(User $user): void;
    public function updateStatus(int $id, bool $blocked): bool;
}
