<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Domain\Events\UserStatusChanged;
use App\Modules\UserManager\Domain\Repositories\UserRepositoryInterface;
use App\Modules\UserManager\Domain\ValueObjects\UserId;
use App\Modules\UserManager\Infrastructure\Models\UserModel;

final class UpdateUserStatusUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * Массовая блокировка/разблокировка пользователей.
     *
     * @param  array<int, int|string>  $ids
     * @param  int  $currentUserId  id текущего админа — для защиты от самоблокировки
     *
     * @return array{count: int, names: array<int, string>}
     */
    public function execute(array $ids, bool $blocked, int $currentUserId): array
    {
        $count = 0;
        $names = [];

        foreach ($ids as $id) {
            $userId = UserId::fromInt((int) $id);

            // Последний рубеж защиты от самоблокировки. Контроллер уже
            // отклоняет такие запросы с 422, но при обходе проверки
            // (строковые id в form-encoded теле, прямой вызов use case)
            // собственный аккаунт здесь пропускается — блокировать себя
            // нельзя ни при каком типе payload.
            if ($blocked && $userId->value === $currentUserId) {
                continue;
            }

            $user = $this->users->findById($userId);

            if ($user && $this->users->updateStatus($userId, $blocked)) {
                $count++;
                $names[] = $user->name;

                // Блокировка отзывает все выпущенные Sanctum-токены —
                // иначе заблокированный пользователь сохраняет API-доступ
                // бессрочно, т.к. проверка blocked происходит только при логине.
                if ($blocked) {
                    UserModel::whereKey($userId->value)->first()?->tokens()->delete();
                }

                event(new UserStatusChanged($user, $blocked));
            }
        }

        return ['count' => $count, 'names' => $names];
    }
}
