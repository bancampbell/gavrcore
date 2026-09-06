<?php

namespace App\Modules\UserManager\Domain\Repositories;

use App\Modules\UserManager\Application\DTO\CreateGroupData;
use App\Modules\UserManager\Application\DTO\GroupFiltersData;
use App\Modules\UserManager\Application\DTO\UpdateGroupData;
use App\Modules\UserManager\Domain\Entities\Group;
use App\Modules\UserManager\Domain\ValueObjects\GroupId;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface GroupRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Group>
     */
    public function paginate(GroupFiltersData $filters): LengthAwarePaginator;

    /**
     * @return Collection<int, Group>
     */
    public function getAll(): Collection;

    public function findById(GroupId $id): ?Group;

    public function create(CreateGroupData $data): Group;

    public function update(GroupId $id, UpdateGroupData $data): Group;

    public function delete(GroupId $id): bool;

    public function updateStatus(GroupId $id, bool $status): bool;
}
