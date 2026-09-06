<?php

namespace App\Modules\UserManager\Domain\Entities;

use App\Modules\UserManager\Domain\ValueObjects\Email;
use App\Modules\UserManager\Domain\ValueObjects\UserId;
use App\Modules\UserManager\Domain\ValueObjects\Username;

final class User
{
    /**
     * @param  array<int, Group>  $groups
     */
    public function __construct(
        public readonly ?UserId $id,
        public readonly string $name,
        public readonly Username $username,
        public readonly Email $email,
        public readonly bool $blocked,
        public readonly bool $activated,
        public readonly array $groups = [],
        public readonly ?string $lastLoginAt = null,
        public readonly ?string $lastLoginIp = null,
        public readonly ?string $createdAt = null,
    ) {
    }

    /**
     * @return array<int, int>
     */
    public function groupIds(): array
    {
        return array_values(array_filter(array_map(
            fn (Group $group) => $group->id?->value,
            $this->groups
        )));
    }

    public function isBlocked(): bool
    {
        return $this->blocked;
    }

    public function isActivated(): bool
    {
        return $this->activated;
    }
}
