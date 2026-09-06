<?php

namespace App\Modules\UserManager\Domain\Entities;

use App\Modules\UserManager\Domain\ValueObjects\AccessLevelId;
use App\Modules\UserManager\Domain\ValueObjects\Alias;

final class AccessLevel
{
    /**
     * Стандартные уровни, создаваемые миграцией. Публичная часть резолвит
     * доступ по алиасу (public, guest, registered и т.д.) — смена алиаса
     * или удаление такого уровня разрывает эти привязки. Запрещено,
     * по аналогии с Group::SYSTEM_ALIASES.
     *
     * @var array<int, string>
     */
    public const SYSTEM_ALIASES = ['public', 'guest', 'registered', 'special', 'super-users'];

    /**
     * @param  array<int, Group>  $groups
     */
    public function __construct(
        public readonly ?AccessLevelId $id,
        public readonly string $title,
        public readonly Alias $alias,
        public readonly ?string $description,
        public readonly int $ordering,
        public readonly bool $status,
        public readonly array $groups = [],
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

    public function isActive(): bool
    {
        return $this->status;
    }

    public function isSystem(): bool
    {
        return in_array($this->alias->value, self::SYSTEM_ALIASES, true);
    }
}
