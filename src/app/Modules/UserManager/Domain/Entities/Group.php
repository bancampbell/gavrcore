<?php

namespace App\Modules\UserManager\Domain\Entities;

use App\Modules\UserManager\Domain\ValueObjects\Alias;
use App\Modules\UserManager\Domain\ValueObjects\GroupId;

final class Group
{
    /**
     * Системные группы, создаваемые миграцией и участвующие в
     * привязке уровней доступа. Удаление любой из них каскадно
     * лишает пользователей прав (в т.ч. admin.access) — запрещено.
     *
     * @var array<int, string>
     */
    public const SYSTEM_ALIASES = ['administrators', 'managers', 'registered', 'public', 'guest'];

    /**
     * @param  array<int, int>  $permissionIds
     */
    public function __construct(
        public readonly ?GroupId $id,
        public readonly string $name,
        public readonly Alias $alias,
        public readonly ?string $description,
        public readonly bool $status,
        public readonly int $ordering,
        public readonly array $permissionIds = [],
        public readonly ?string $createdAt = null,
    ) {
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
