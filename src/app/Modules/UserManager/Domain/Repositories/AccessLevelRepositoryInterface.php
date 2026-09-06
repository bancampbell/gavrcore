<?php

namespace App\Modules\UserManager\Domain\Repositories;

use App\Modules\UserManager\Application\DTO\AccessLevelFiltersData;
use App\Modules\UserManager\Application\DTO\CreateAccessLevelData;
use App\Modules\UserManager\Application\DTO\UpdateAccessLevelData;
use App\Modules\UserManager\Domain\Entities\AccessLevel;
use App\Modules\UserManager\Domain\ValueObjects\AccessLevelId;
use Illuminate\Support\Collection;

interface AccessLevelRepositoryInterface
{
    /**
     * @return Collection<int, AccessLevel>
     */
    public function getAll(AccessLevelFiltersData $filters): Collection;

    public function findById(AccessLevelId $id): ?AccessLevel;

    public function create(CreateAccessLevelData $data): AccessLevel;

    public function update(AccessLevelId $id, UpdateAccessLevelData $data): AccessLevel;

    public function delete(AccessLevelId $id): bool;

    public function updateStatus(AccessLevelId $id, bool $status): bool;

    /**
     * @param  array<int, array{id: int, ordering: int}>  $order
     */
    public function updateOrdering(array $order): bool;
}
