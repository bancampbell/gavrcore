<?php

namespace App\Console\Commands;

use App\Modules\SettingManager\Application\Services\SettingService;
use App\Modules\UserManager\Infrastructure\Models\GroupModel;
use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Database\Seeders\DemoSeeder;
use Database\Seeders\EssentialSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Интерактивная установка GavrCore CMS по образцу современных CMS:
 * минимум вопросов (креды администратора + опционально название сайта),
 * миграции, Essential-сеeding, итоговый вывод. Демо-контент — флагом --demo.
 *
 * Идемпотентна-защищена: повторный запуск на установленную систему
 * честно сообщает «Уже установлено» и ничего не дублирует.
 */
class GavrInstallCommand extends Command
{
    protected $signature = 'gavr:install
        {email? : Email администратора}
        {--name= : Имя администратора}
        {--password= : Пароль администратора (иначе ADMIN_PASSWORD из .env или автогенерация)}
        {--site-name= : Название сайта (пусто — пропустить, настройка создастся лениво)}
        {--demo : Дополнительно загрузить демо-контент (DemoSeeder)}
        {--force : Без интерактивных вопросов (неинтерактивный режим)}';

    protected $description = 'Интерактивная установка GavrCore CMS (миграции + обязательные сиды)';

    public function handle(): int
    {
        $this->info('Установка GavrCore CMS');

        // Чистый .env из шаблона не содержит APP_KEY — без него сессии
        // и шифрование не работают (вход в админку сломался бы).
        if (empty(config('app.key'))) {
            $this->call('key:generate', ['--force' => true]);
        }

        try {
            [$email, $name, $password] = $this->askAdminCredentials();
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $siteName = $this->resolveSiteName();

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Некорректный email: {$email}");

            return self::FAILURE;
        }

        // Защита от повторной установки — до любых изменений в БД.
        // Сначала точное совпадение по email, затем любой существующий
        // администратор (не дублируем пользователей при другом email).
        if (Schema::hasTable('users')) {
            if (UserModel::where('email', $email)->exists()) {
                $this->warn("Уже установлено: пользователь с email {$email} уже существует.");
                $this->warn('Для переустановки с нуля: php artisan migrate:fresh, затем повторите gavr:install.');

                return self::SUCCESS;
            }

            $adminGroup = GroupModel::where('alias', 'administrators')->first();

            if ($adminGroup !== null && ($existing = $adminGroup->users()->first()) !== null) {
                $this->warn("Уже установлено: администратор ({$existing->email}) существует.");
                $this->warn('Для переустановки с нуля: php artisan migrate:fresh, затем повторите gavr:install.');

                return self::SUCCESS;
            }
        }

        // Миграции. БД не пуста — только с явного подтверждения migrate:fresh.
        if ($this->databaseIsEmpty()) {
            $this->call('migrate', ['--force' => true]);
        } else {
            $confirmed = $this->confirm(
                'База данных не пуста. Выполнить migrate:fresh? ВСЕ существующие данные будут удалены!',
                false
            );

            if (! $confirmed) {
                $this->warn('Установка отменена — данные не тронуты.');

                return self::FAILURE;
            }

            $this->call('migrate:fresh', ['--force' => true]);
        }

        $this->info('Создание обязательных данных: группы, права, администратор, настройки...');

        $seeder = new EssentialSeeder($email, $name, $password);
        $seeder->setCommand($this);
        $seeder->run();

        if ($siteName !== null && $siteName !== '') {
            app(SettingService::class)->updateSettings(['site_name' => $siteName]);
        }

        if ($this->option('demo')) {
            $this->info('Загрузка демо-контента (только для разработки)...');
            $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);
        }

        $this->newLine();
        $this->info('Установка завершена.');
        $this->line("Вход в админку: {$email} (пароль — заданный выше или сгенерированный, см. вывод команды).");
        $this->warn('Смените пароль после первого входа.');

        if (! $this->option('demo')) {
            $this->line('Демо-контент (опционально): php artisan db:seed --class=DemoSeeder');
        }

        return self::SUCCESS;
    }

    /**
     * @return array{string, string, ?string}
     */
    private function askAdminCredentials(): array
    {
        $email = $this->argument('email');
        $name = $this->option('name');
        $password = $this->option('password');

        if (! $this->isInteractive()) {
            return [
                $email ?: (env('ADMIN_EMAIL') ?: 'admin@example.com'),
                $name ?: (env('ADMIN_NAME') ?: 'Administrator'),
                $password ?: null,
            ];
        }

        $email = $email ?? $this->ask('Email администратора', env('ADMIN_EMAIL') ?: 'admin@example.com');
        $name = $name ?? $this->ask('Имя администратора', env('ADMIN_NAME') ?: 'Administrator');

        if ($password === null) {
            $password = $this->secret('Пароль администратора (Enter — взять из ADMIN_PASSWORD или сгенерировать)');
            $password = $password ?: null;

            if ($password !== null) {
                $confirmed = $this->secret('Повторите пароль');

                if ($confirmed !== $password) {
                    throw new RuntimeException('Пароли не совпадают.');
                }
            }
        }

        return [$email, $name, $password];
    }

    private function resolveSiteName(): ?string
    {
        $siteName = $this->option('site-name');

        if ($siteName === null && $this->isInteractive()) {
            $siteName = $this->ask('Название сайта (Enter — пропустить)', '');
        }

        return $siteName;
    }

    private function isInteractive(): bool
    {
        return ! $this->option('force') && $this->input->isInteractive();
    }

    private function databaseIsEmpty(): bool
    {
        if (! Schema::hasTable('users')) {
            return true;
        }

        return UserModel::count() === 0;
    }
}
