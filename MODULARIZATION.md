# Миграция resources/js в модульность

Правила: одна задача = одна сессия. Только перенос файлов + обновление импортов, НИКАКОГО рефакторинга.
После каждой задачи: npx vitest run resources/js/__tests__ (весь сьют зелёный) + npm run build (успешно) → отдельный коммит → отметить [x] с датой. Не закрывать задачу без зелёных прогонов.

## Фаза 0. Мёртвый код (1 сессия)
- [x] T0.1 Удалить: layouts/EmptyLayout.vue (ветка в LayoutSwitcher недостижима — currentTheme ∈ {default,warm,landing}; удалить и саму ветку из map), пустые папки: api/, Pages/Themes/Landing/, components/shared/Editor/composables/. Проверка: grep ссылок на удалённое, vitest, build. — 2026-11-07

## Фаза 1. Одиночные потребители (1 сессия)
- [x] T1.1 Перенести layouts/DashboardLayout.vue → modules/UserDashboard/ui/layouts/DashboardLayout.vue. Обновить импорты (6 страниц UserDashboard + grep по проекту). Проверка: vitest, build, кабинет открывается визуально. — 2026-11-07
- [x] T1.2 Перенести components/shared/AuthCard.vue → modules/UserManager/ui/Auth/AuthCard.vue. Обновить импорты (Login.vue, Register.vue). Проверка: vitest, build, страницы логина/регистрации визуально. — 2026-11-07

## Фаза 2. Editor-кластер → MaterialManager (3 сессии)
Цель: весь поддомен редактора живёт внутри модуля MaterialManager.
- [x] T2.1 Hexagon (только .ts): domain/{ports,values}/**, application/usecases/**, infrastructure/{adapters,di}/** → modules/MaterialManager/{domain,application,infrastructure}/** (зеркалить текущие пути). Обновить импорты UI/тестов на новые пути. Проверка: vitest, build. UI и тесты пока не двигаем. — 2026-11-07
- [x] T2.2 UI: components/shared/Editor/** → modules/MaterialManager/ui/editor/; components/shared/{FormSelectModal,GallerySelectModal}.vue → modules/MaterialManager/ui/editor/modals/; composables/useEditor{,Commands,Selection}.ts → modules/MaterialManager/ui/editor/composables/. Обновить импорты (Edit.vue, Create.vue, тесты). Проверка: vitest, build, визуально: редактор открывается, тулбар, вставка галереи/формы. — 2026-11-07
- [x] T2.3 Тесты: __tests__/{application,composables,editor,infrastructure}/** → __tests__/modules/material-manager/editor/**. Обновить пути импортов. Проверка: vitest (весь сьют). — 2026-11-07

## Фаза 3. Публичный сайт → modules/Site (после фаз 0–2, по одной задаче за сессию)
- [x] T3.0 Контракт переноса публичного сайта в modules/Site (задача-план, без кода) — 2026-11-07

  Целевая структура (пути строго по правилу резолвера app.ts: './modules/<ИмяМодуля>/ui/<остальные-сегменты-имени>.vue'):
  ```
  modules/Site/
  ├── ui/Home.vue                        Inertia-имя: 'Site/Home'          (сейчас Pages/Index.vue, рендерится как 'Index')
  ├── ui/Category/Show.vue               Inertia-имя: 'Site/Category/Show' (сейчас Pages/Category/Show.vue)
  ├── ui/Search/Index.vue                Inertia-имя: 'Site/Search/Index'  (сейчас Pages/Search/Index.vue)
  ├── ui/Landing/Index.vue               Inertia-имя: 'Site/Landing/Index' (сейчас Pages/landing/Index.vue)
  ├── ui/Errors/{Forbidden,NotFound,ServerError,TooManyRequests}.vue
  │                                      Inertia-имя: 'Site/Errors/<Name>' (сейчас Pages/Errors/*: Forbidden, 429→TooManyRequests, NotFound, ServerError)
  └── ui/components/Breadcrumbs.vue      (компонент, не страница — правило резолвера на него не распространяется; уезжает из components/shared — потребители: Pages/*)
  ```

  Правила переименования:
  1. Только переименование путей/имён Inertia-страниц — никакой логики в контроллерах, middleware, обработчиках.
  2. Компонент 429.vue переименовать файлом в TooManyRequests.vue (имя класса/страницы чище); вызовы обновить.
  3. app.ts: glob './Pages/**' (строка 7) удаляется ТОЛЬКО в T3.3 (последний перенос), до этого Pages glob остаётся — одна страница не должна резолвиться из двух мест одновременно. В T3.1–T3.2 после переноса старые файлы удаляются, так что коллизии нет; glob физически пустеет к T3.3.
  4. MenuItem.vue и прочие shared-компоненты тем НЕ трогаем (у них потребители themes/*).
  5. После T3.3 папка Pages/ должна быть удалена полностью (пустая).
- [x] T3.1 Errors: Pages/Errors/{Forbidden,NotFound,ServerError}.vue → modules/Site/ui/errors/; Pages/Errors/429.vue → modules/Site/ui/errors/TooManyRequests.vue (переименование файлом). Обновить PHP-точки (только строки Inertia::render, логику не трогать):
  - src/app/Modules/UserManager/Infrastructure/Http/Middleware/AdminMiddleware.php:40 — 'Errors/Forbidden' → 'Site/Errors/Forbidden';
  - src/bootstrap/app.php:85 — 'Errors/429' → 'Site/Errors/TooManyRequests';
  - src/bootstrap/app.php:99 — 'Errors/NotFound' → 'Site/Errors/NotFound';
  - src/bootstrap/app.php:116 — 'Errors/ServerError' → 'Site/Errors/ServerError'.
  Старые файлы в Pages/Errors/ удалить. app.ts resolver НЕ менять (Pages glob остаётся). Проверка: vitest, build, страницы 403/404/429/500 визуально. — 2026-11-07
- [x] T3.2 Index/Category/Search + Breadcrumbs:
  - Pages/Index.vue → modules/Site/ui/Home.vue (Inertia-имя 'Index' → 'Site/Home');
  - Pages/Category/Show.vue → modules/Site/ui/Category/Show.vue ('Category/Show' → 'Site/Category/Show');
  - Pages/Search/Index.vue → modules/Site/ui/Search/Index.vue ('Search/Index' → 'Site/Search/Index');
  - components/shared/Breadcrumbs.vue → modules/Site/ui/components/Breadcrumbs.vue; обновить импорты (Home.vue — './components/Breadcrumbs.vue', Category/Show.vue и Search/Index.vue — '../components/Breadcrumbs.vue') и в modules/MaterialManager/ui/Show.vue:67 — '@/modules/Site/ui/components/Breadcrumbs.vue'.
  Обновить Inertia::render в src/app/Modules/MaterialManager/Infrastructure/Http/Controllers/WebMaterialController.php (найден grep'ом по src/app): строка 121 'Index' → 'Site/Home', строка 204 'Category/Show' → 'Site/Category/Show', строка 238 'Search/Index' → 'Site/Search/Index' (строка 52 'landing/Index' НЕ трогать — это T3.3). app.ts resolver НЕ менять. Старые файлы удалить. Проверка: vitest, build, публичные страницы визуально. — 2026-11-07
- [ ] T3.3 landing: Pages/landing/Index.vue → modules/Site/ui/landing/Index.vue. Обновить Inertia::render в src/app/Modules/MaterialManager/Infrastructure/Http/Controllers/WebMaterialController.php:52 — 'landing/Index' → 'Site/Landing/Index'. Удалить glob './Pages/**/*.vue' в app.ts (строка 7) и удалить пустую папку Pages/ целиком. Проверка: build, лендинг визуально.

## После всех фаз
- [ ] T4.1 Финальная проверка: вне modules/ остались только app.ts, d.ts, layouts/{AdminLayout,LayoutSwitcher,Default,Warm,Landing}, components/Admin/**, components/shared/{Toast,ToastContainer,ConfirmModal,CookieConsent,GlobalLightbox,ShortcodeRenderer,MenuItem}, composables/{useToast,useSidebarSections}, services/, themes/, css/, types/, __tests__ (только modules/). Аудит повторить (тот же промт), сверить.