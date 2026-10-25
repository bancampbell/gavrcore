# AUDIT.md — полный аудит gavrcore перед деплоем

Дата: 2026-10-25. Режим: строго read-only (ни один файл проекта не изменён; единственная запись — этот отчёт).
Стек: Laravel 13.11.2, PHP (docker-контейнер `gavrcore_app`), Postgres, Inertia + Vue 3 + TS, nginx.
Метод: (1) механический GET-обход всех 66 GET|HEAD-роутов тремя ролями (гость / обычный пользователь id=3 / admin id=1) через HTTP с сессиями; (2) POST-проверки persistence ключевых форм; (3) проверка Inertia-props на 10 страницах; (4) статический анализ блокеров деплоя (сидеры, хардкод, APP_DEBUG, .env.example, мёртвый код); (5) верификация известных багов. Все критические находки перепроверены чтением кода в день отчёта.

Окружение обхода: nginx localhost:80 → gavrcore_app; БД gavrcore. Динамические сегменты подставлены реальными значениями (материал `programma-dolgosrocnyx-sberezenii-pds` id=1, категория-алиас `novosti` id=2, меню `main-menu` id=1 и т.д.).

## Сводка по серьёзностям

| Серьёзность | Кол-во |
|---|---|
| BLOCKER | 4 |
| MAJOR | 12 |
| MINOR | 14 |
| Опровергнуто (не баги) | 2 |

## Каталог находок

| ID | Модуль | Серьёзность | Где (URL или файл) | Шаги воспроизведения | Ожидал / Факт | Вероятная причина (файл:строка) |
|---|---|---|---|---|---|---|
| AUDIT-01 | UserManager (seeders) | BLOCKER | `src/app/Modules/UserManager/Database/Seeders/UserGroupPermissionSeeder.php` | Запустить `migrate --seed` (или `db:seed`) на чистой прод-БД | Сидеры безопасны для прода / Создаётся admin с известным паролем `admin123` (manager — `manager123`) | Хардкод паролей в firstOrCreate: `:77` (admin), `:90` (manager). firstOrCreate не перезапишет существующего, но на чистой БД создаст |
| AUDIT-02 | Core (bootstrap) | BLOCKER | `src/bootstrap/app.php:75-119`; любой несуществующий URL | `GET /no-such-page-xyz` | HTTP 404 / HTTP 200 с компонентом `Errors/NotFound`. То же для 429 (`:85`) и 500 (`:116`) — рендер без статуса | `Inertia::render(...)->toResponse($request)` не передаёт код статуса. API-404 корректны (404). Ломает SEO/мониторинг/кэш и маскирует abort(404) |
| AUDIT-03 | MaterialManager / Core | BLOCKER | `/category/{slug}`, `/search`, throttle-429; `src/app/Modules/MaterialManager/Infrastructure/Http/Controllers/WebMaterialController.php:200,:234`, `src/bootstrap/app.php:85` | Открыть `/category/novosti` или `/search` | Страница категории/поиска / HTTP 500: Inertia-страниц `Pages/Category/Show.vue` и `Pages/Search/Index.vue` нет в проекте (в `Pages/` только `Errors/{NotFound,ServerError}.vue` — 429.vue тоже нет). Роуты зарегистрированы, рендер падает | Компоненты не созданы; роуты не убраны |
| AUDIT-04 | Репозиторий | BLOCKER | git-индекс: `src/test_files_U.txt` (357 КБ), `src/sions:n";` (16 КБ), `src/Admin,`, `src/admin@example.com,`, `src/build`, `src/vite` (0 Б), `src/project_structure.txt`, `src/project_tree.txt` | `git ls-files` — всё закоммичено, в .gitignore не покрыто | Чистый репозиторий / В репозитории мусор, включая полный слепок исходников проекта; всё уедет в прод-деплой | Артефакты битых редиректов/вывода `tree`/`less`, закоммичены по ошибке |
| AUDIT-05 | MaterialManager (seeders) | MAJOR | `src/database/seeders/MaterialSeeder.php:55` | Повторно запустить сидер | Идемпотентность / Каждый запуск вставляет 18 материалов с Lorem ipsum (`:59`); повторный прогон упадёт на уникальности slug | `MaterialModel::create()` без firstOrCreate/updateOrCreate. Комментарий в шапке файла утверждает обратное — тоже исправить |
| AUDIT-06 | MenuManager (seeders) | MAJOR | `src/database/seeders/MenuSeeder.php:14` | Повторно запустить сидер | Идемпотентность / Демо-меню дублируется каждый запуск | `MenuTypeModel::create()` без проверки |
| AUDIT-07 | FormBuilder (seeders) | MAJOR | `src/app/Modules/FormBuilder/Database/Seeders/FormSubmissionsSeeder.php:69` | Повторно запустить сидер | Идемпотентность / +4 фейковых обращения каждый запуск | `FormSubmissionModel::create()` в цикле без проверки |
| AUDIT-08 | CategoryManager (seeders) | MAJOR | `src/database/seeders/CategorySeeder.php:22` | Запустить сидер на БД с продуктовыми категориями | Сидер только дополняет данные / updateOrCreate по alias перезапишет `is_active=true` и description у совпавших категорий | `updateOrCreate(['alias' => ...], ['is_active' => true, ...])` |
| AUDIT-09 | MenuManager | MAJOR | `src/resources/js/modules/MenuManager/ui/components/MaterialSelectorModal.vue:91`; `src/routes/api.php` (пуст) | В админке: редактирование пункта меню → «Выбрать материал» | Список материалов в модалке / Пусто («Материалы не найдены»): `GET /api/materials` → 404 (маршрута не существует), `Authorization: Bearer null` из localStorage, catch обнуляет список | Модалка тянет несуществующий API вместо рабочего `/admin/materials/list`; формат ответа тоже не совпадает (модалка ждёт пагинатор `data.data/current_page/last_page`, эндпоинт отдаёт голый массив — `AdminMaterialQueryController.php:96-108`) |
| AUDIT-10 | UserManager / MenuManager / MaterialManager | MAJOR | `GET /admin/users/abc/edit`, `GET /admin/materials/abc/edit`, `GET /admin/menu/types/abc/items/tree` | Подставить нечисловой сегмент вместо id | HTTP 404 / HTTP 500 TypeError «Argument #1 ($id) must be of type int, string given» | Типизация `int $id` в контроллерах без `whereNumber`/валидации: `AdminUserQueryController.php:57`, `AdminMenuItemQueryController.php:66` и др. |
| AUDIT-11 | Core (config) | MAJOR | `src/config/cors.php:6` | Проверить CORS-заголовки ответа API с прод-домена | CORS настроен под прод / `allowed_origins` = дефолт `http://localhost:3000`: ключа `FRONTEND_URL` нет ни в `.env`, ни в `src/.env` | `env('FRONTEND_URL', 'http://localhost:3000')` без значения в окружении |
| AUDIT-12 | SettingManager | MAJOR | props `appSettings` всех страниц; `src/app/Modules/SettingManager/Application/Services/SettingService.php:68` | Открыть любую страницу, посмотреть `appSettings.site_name` | Значение по умолчанию / null везде: дефолтные настройки никогда не инициализируются в БД (сидирована только `homepage_type`) | `initializeDefaultSettings()` не вызывается ни в сидерах, ни при деплое |
| AUDIT-13 | SettingManager / homepage | MAJOR | `GET /`, props `homepageMaterial` | Открыть главную при `homepage_type=material` | Главный материал на главной / `homepageMaterial` = null, главная рендерится без главного материала | Материал главной не назначен; нет интерфейса/сидера назначения |
| AUDIT-14 | UserManager | MAJOR | `POST /admin/login` | Войти в админ-форму обычным пользователем | Отказ в логине / 302 → /admin/dashboard: логин проходит (use case проверяет только blocked/activated). Эскалации нет — AdminMiddleware редиректит на /, но сама форма не-админов не отвергает | `LoginUserUseCase.php:12-46` — проверки isAdmin нет |
| AUDIT-15 | UserManager | MAJOR | `POST /login` (WebAuthController) | Войти через публичную форму | Метаданные логина пишутся как в /admin/login / `last_login_at`/`last_login_ip` не обновляются | `WebAuthController.php:34-81` не пишет last_login_* (в отличие от LoginUserUseCase.php:40-43) |
| AUDIT-16 | SettingManager | MAJOR | `POST /admin/settings` | Попытаться обновить `homepage_type` через API настроек | Ключ обновляется / Ключ вне белого списка валидации контроллера — обновить тип главной через эндпоинт нельзя (в БД значение сидировано напрямую) | Список правил `AdminSettingController.php:34-54` не включает `homepage_type` |
| AUDIT-17 | MediaManager | MINOR | `GET /admin/media*` гостем | Открыть админ-роуты медиа без авторизации | 302 → /login как везде в admin/* / 403 (Gate `manage-media` отрабатывает до редиректа) | `can:manage-media` на роутах `MediaManager/Routes/web.php:6` вместо admin-middleware |
| AUDIT-18 | UserManager | MINOR | обычный юзер на `GET /admin/*` | Открыть админ-страницу пользователем без прав | 403 / 302 на /. На `/admin/categories*` и `/admin/menu/*` — честные 403 через policy (эти группы без AdminMiddleware) | `AdminMiddleware.php:34-37` редиректит вместо 403 |
| AUDIT-19 | CategoryManager | MINOR | props `categories[*].slug` | Читать props на любой странице со списком категорий | Заполненный slug / null у всех (сидированных и созданных): публичный роут резолвится по `alias`, колонка slug фактически мёртвая | Слаг не заполняется ни сидерами, ни при создании категории |
| AUDIT-20 | GalleryManager | MINOR | `GET /galleries/1` | Открыть галерею | Галерея отображается / 404 все роли: таблица galleries пуста, при этом файлы лежат в `storage/app/public/galleries/4` (данные рассинхронизированы) | Сидер галерей отсутствует/пустой |
| AUDIT-21 | MaterialManager | MINOR | `DELETE /admin/materials/{id}` | Удалить материал штатным REST-способом | 200/204 / 405: отдельного destroy-роута нет, удаление только через `POST /admin/materials/bulk-trash` (мягкое) | По design (`MaterialManager/Routes/admin.php:26-36`), но 405 удивляет |
| AUDIT-22 | FormBuilder | MINOR | публичные страницы | Найти сидированную страницу с формой обратной связи | Есть демо-форма на сайте / Ни один материал не содержит form-шорткод; эндпоинты `api/forms/{id}` работают, механизм (FormRenderer/ShortcodeRenderer) не продемонстрирован | Сидеры не создают демо-страницу с шорткодом |
| AUDIT-23 | Core | MINOR | `src/.env.example:4` | Сравнить шаблон с прод-требованиями | APP_DEBUG=false в шаблоне / `APP_DEBUG=true` — риск включённого дебага при развёртывании по шаблону | Шаблон не приведён к прод-умолчаниям |
| AUDIT-24 | Core | MINOR | `src/.env` ↔ `src/.env.example` | Сверить ключи | Ключи синхронны / В `.env` есть legacy-ключ `MAIL_ENCRYPTION` (Laravel 10, не читается фреймворком — мусор); в `.env` нет `MAIL_SCHEME`, который есть в шаблоне. Корневой `.env.example` (3 строки DB_*) — шаблон docker-compose, не приложения | Расхождение ключей при переездах между версиями Laravel |
| AUDIT-25 | Frontend (мёртвый код) | MINOR | `resources/js`: `modules/UserDashboard/ui/Tickets.vue`, `components/Admin/AdminSidebarMenu.vue`, `modules/MenuManager/ui/components/MenuItemRow.vue`, `modules/GalleryManager/ui/components/SwitcherGallery.vue`, дубль `FormWrapper.vue`, цепочка `services/{HttpClient,container,types,LightboxService}.ts` + `domain/ports/ILightboxService.ts`, дубли `useGalleryParser.ts` (2 шт.), `domain/services/ImageStyleParser.ts`, `domain/values/NodePosition.ts`, `modules/MediaManager/routes/index.ts` | Поиск ссылок/импортов на файлы | Импортируемый код / Ни одной ссылки — файлы мёртвы, раздувают сборку и сбивают с толку | Остатки рефакторингов |
| AUDIT-26 | Репозиторий | MINOR | `src/src/`, корневой `resources/` | Найти файлы в каталогах | Рабочие каталоги / 0 файлов — пустые скелеты, удалить | Остатки переноса структуры |
| AUDIT-27 | Editor (TipTap) | MINOR | `resources/js/infrastructure/adapters/TiptapLinkAdapter.ts:66-70` | Вставить ссылку с кавычками в URL/title | Экранирование атрибутов / URL и title интерполируются в HTML без экранирования кавычек | `buildLinkHtml` шаблонная строка без escape |
| AUDIT-28 | MaterialManager | MINOR | `POST /admin/materials` | Создать материал, смотреть код ответа | 201 Created / 200 `{"success":true,"id":...}` | Контроллер возвращает 200 вместо 201 |

## Результаты HTTP-обхода (66 GET-роутов × 3 роли)

Полный код-матрица (гость/user/admin) — ниже сжато; в обходе ни одного реального 500 при корректных значениях сегментов.

- **Авторизация в целом корректна**: гость на защищённых → 302 /login (кроме `admin/media/*` → 403, AUDIT-17); юзер без прав на admin/* → 302 / (AUDIT-18), на categories/menu → 403; доступа к чужим заявкам нет (abort_unless, DashboardController.php:85 — маскируется AUDIT-02, но доступа нет); CSRF работает (419 без токена).
- **POST-persistence подтверждена** (данные сверены SELECT'ами): логин admin (last_login_* пишутся), обратная связь (submission_id=5 в БД), заявка из кабинета (id=6, user_id=3, видна в списке и карточке), создание/редактирование материала (id=19), категории (201, id=7), пункта меню (id=4), настройки (`site_name` создан; исходное состояние восстановлено). Тестовые сущности удалены штатными эндпоинтами; тестовый юзер id=3 оставлен (самоудаления нет), сабмишены 5/6 — штатные данные рабочего процесса.
- **Inertia props**: списки не null (`materials[10]`, `categories[7]`, `forms[2]`, `menuTypes[3]`, `submissions[1]`); аномалии — AUDIT-12 (`site_name` null), AUDIT-13 (`homepageMaterial` null), AUDIT-19 (`slug` null). Ожидаемые null (meta, published_at у draft, верификация email) — не находки.

## Верификация известных багов

Файл `BUGS.md` в корне проекта **отсутствует** — верифицированы два пункта, названные в задании:

1. **«Ссылка в TipTap не сохраняется» — ОПРОВЕРГНУТО.** HTTP-репро с admin-сессией: `PUT /admin/materials/18` с `<a href target title>` → 200, в БД контент сохранился побайтово (после проверки восстановлен). Runtime-репро фронта: TipTap v3.23.6 (конфиг из `Editor/Index.vue`) под jsdom — 5/5 сценариев (вставка через диалог, v-model round-trip, относительный URL, загрузка из БД, редактирование ссылки) с href на месте; unit-тесты адаптера 4/4. Бэкенд HTML не санитизит (`UpdateMaterialRequest.php:43` — просто `nullable|string`), Link-расширение хранит атрибуты, `ShortcodeRenderer.vue:103` разрешает `a[href]`. Дефекта нет; см. лишь эдж-кейс AUDIT-27.
2. **«Модалка выбора материала в меню пустая» — ПОДТВЕРЖДЕНО.** `GET /api/materials` → 404; рабочий `GET /admin/materials/list` → 200 (17 материалов). Диагноз — AUDIT-09. Edit.vue:486/Create.vue:514 используют рабочий эндпоинт; GallerySelectModal/FormSelectModal целы. Тот же Bearer-из-localStorage анти-паттерн ещё встречается в AdminLayout/EmptyLayout, category-api, SettingManager — но к сессионным `/admin/*` URL, безвреден.

## Опровергнутые находки аудита

- **«MediaManager мёртв, все роуты /admin/media* → 500»** — НЕ ВЕРНО. Namespace `Modules\...` в `MediaManager/Routes/web.php:4` корректен: composer.json маппит psr-4 `Modules\` → `app/Modules/`. Эмпирика обхода: admin на `admin/media*` → 200. Находка снята.
- **«Ссылка в TipTap не сохраняется»** — см. раздел выше, опровергнуто воспроизведением.

## Чистые проверки (находок нет)

- Хардкод `localhost`/`127.0.0.1` в `app/`, `routes/`, `database/`, `resources/js` — 0 вхождений (кроме следствия AUDIT-11).
- `dd()/dump()/var_dump/var_export/print_r` в не-тестовом коде — 0; `->log(` — только spatie activitylog/audit.
- `APP_DEBUG` в коде — не читается; дефолт `config/app.php:42` = false.
- Привязка контроллеров к роутам: все 35 контроллеров на роутах, методы покрыты.

## Вердикт

**К деплою НЕ ГОТОВ.** Минимум перед выкаткой: убрать дефолтные пароли из сидеров и исключить seeders из прод-деплоя (AUDIT-01); починить статусы ошибочных страниц (AUDIT-02); создать/удалить битые Inertia-страницы (AUDIT-03); вычистить мусор из git-индекса (AUDIT-04); задать `FRONTEND_URL` и прод-секреты в окружении (AUDIT-11, AUDIT-23, AUDIT-24); починить модалку выбора материала (AUDIT-09). Полный прогон тестов и `php artisan config:cache` на прод-конфиге обязателен после правок.
