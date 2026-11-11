# Ежедневные команды GavrCore

Шпаргалка по командам, реально используемым в проекте. Контейнер приложения — `gavrcore_app`, код — в `src/`.

### Окружение

```bash
docker compose up -d                 # поднять окружение
docker compose down                  # остановить
docker compose up -d --build         # пересборка образа (после правок Dockerfile/зависимостей)
```

### Установка

```bash
docker exec -it gavrcore_app php artisan gavr:install   # интерактивно (email/пароль админа, название сайта)
docker exec gavrcore_app php artisan gavr:install admin@example.com --name=Admin --password=secret --force  # неинтерактивно
docker exec -it gavrcore_app php artisan gavr:install --demo  # сразу с демо-контентом
docker exec -it gavrcore_app php artisan db:seed --class=DemoSeeder  # демо-контент позже, на установленную систему
```

### Проверки (прогоны)

```bash
docker exec gavrcore_app php artisan test             # ПОЛНЫЙ прогон бэкенд-тестов — обязателен после бэкенд-правок
docker exec gavrcore_app php artisan test --filter=GavrInstallCommandTest  # один тест/класс при отладке
cd src && npm run test:run                            # vitest — С ХОСТА, из src/ (в fpm-контейнере jsdom-окружения нет)
docker exec gavrcore_app npm run build                # сборка фронтенда — в контейнере (там Node и единый node_modules)
```

### База данных

```bash
docker exec -it gavrcore_app php artisan migrate                    # накатить миграции
docker exec -it gavrcore_app php artisan migrate:fresh              # ВНИМАНИЕ: сносит всю БД
docker exec -it gavrcore_app php artisan migrate:rollback --step=1  # откатить последнюю миграцию
docker exec -it gavrcore_app php artisan tinker --execute="echo config('app.name');"  # однострочный tinker
```

### Кэш / конфиг

```bash
docker exec gavrcore_app php artisan config:clear   # после правок .env / config
docker exec gavrcore_app php artisan cache:clear    # после правок кэшируемых данных
docker exec -it gavrcore_app php artisan storage:link # симлинк public/storage (один раз после установки)
```

### Git

```bash
git add -A && git commit -m "..."   # обычный коммит
```

Правило: после любых бэкенд-правок перед коммитом — полный `php artisan test` (см. «Проверки»).

### Подводные камни

- vitest запускать только из `src/` на хосте — из корня падает (конфиг `src/vitest.config.ts`).
- `npm run build` — только в контейнере `gavrcore_app`.
- `node_modules` единый через bind-mount (`./src/node_modules`) — `npm install` выполнять только на хосте.
- Полный прогон `php artisan test` обязателен после бэкенд-правок, `--filter` — только для локальной отладки.
- Интерактивная кириллица в `docker exec -it` работает: в образе локаль `C.UTF-8`.
