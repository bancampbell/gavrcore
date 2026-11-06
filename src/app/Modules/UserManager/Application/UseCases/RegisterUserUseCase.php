<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Application\DTO\RegisterData;
use App\Modules\UserManager\Domain\ValueObjects\Email;
use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class RegisterUserUseCase
{
    private const MAX_USERNAME_ATTEMPTS = 50;

    public function execute(RegisterData $data): UserModel
    {
        $email = Email::fromString($data->email);

        $base = Str::slug($data->username);

        if ($base === '') {
            $base = 'user';
        }

        $candidate = $base;
        $suffix = 1;
        $attempts = 0;

        while (true) {
            try {
                /** @var UserModel $user */
                $user = UserModel::create([
                    'name' => $data->name,
                    'email' => $email->value,
                    'password' => Hash::make($data->password),
                    'username' => $candidate,
                ]);

                return $user;
            } catch (QueryException $e) {
                if (! $this->isUniqueViolation($e)) {
                    throw $e;
                }

                $message = strtolower($e->getMessage());

                // Гонка по email: оба запроса прошли unique-валидацию,
                // проигравший должен получить 422, а не 500.
                if (str_contains($message, 'email')) {
                    throw ValidationException::withMessages([
                        'email' => ['Email уже зарегистрирован'],
                    ]);
                }

                // Гонка по username: параллельная регистрация заняла этот
                // логин раньше. Идём на следующий свободный суффикс.
                if (str_contains($message, 'username')) {
                    if (++$attempts >= self::MAX_USERNAME_ATTEMPTS) {
                        throw $e;
                    }

                    $candidate = $base.'-'.$suffix;
                    $suffix++;

                    continue;
                }

                // Уникальное нарушение по другому полю — пробрасываем как есть.
                throw $e;
            }
        }
    }

    /**
     * Конфликт по любому уникальному индексу.
     */
    private function isUniqueViolation(QueryException $e): bool
    {
        // MySQL: 1062, PostgreSQL: 23505, SQLite: 8/19/1555/2067 (SQLITE_CONSTRAINT*).
        // Драйвер pgsql кладёт в errorInfo[1] свой код (7), а не SQLSTATE —
        // 23505 ищем в errorInfo[0]/getCode().
        $codes = array_filter([
            (string) ($e->errorInfo[1] ?? ''),
            (string) ($e->errorInfo[0] ?? ''),
            (string) $e->getCode(),
        ]);

        return count(array_intersect($codes, ['1062', '23505', '8', '19', '1555', '2067'])) > 0;
    }
}
