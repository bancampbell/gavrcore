# GavrCore CMS

Модульная CMS на Laravel 13 + Inertia.js/Vue 3 (приложение — в каталоге `src/`, инфраструктура — в `docker/`).

## Установка

```bash
git clone <репозиторий> && cd gavrcore
cp .env.example .env            # переменные Postgres для docker-compose
cp src/.env.example src/.env    # окружение приложения
docker compose up -d --build

docker exec -it gavrcore_app php artisan gavr:install
```

Инсталлятор задаст email/имя/пароль администратора (пароль вводится скрыто, с подтверждением; если оставить пустым — будет сгенерирован и напечатан в консоль), при необходимости — название сайта, сам выполнит миграции и создаст обязательные данные (группы, права, администратор, настройки). Сгенерированный пароль смените после первого входа.

Демо-контент (материалы, категории, галереи, меню, демо-пользователь Manager) — опционально:

```bash
docker exec -it gavrcore_app php artisan gavr:install --demo
# или позже, на установленную систему:
docker exec -it gavrcore_app php artisan db:seed --class=DemoSeeder
```

Сайт: http://localhost, админка: http://localhost/admin.

Полный прогон тестов: `docker exec gavrcore_app php artisan test`, сборка фронтенда: `docker exec gavrcore_app npm run build`.

Ежедневные команды: docs/COMMANDS.md
