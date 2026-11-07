# Миграция resources/js в модульность

Правила: одна задача = одна сессия. Только перенос файлов + обновление импортов, НИКАКОГО рефакторинга.
После каждой задачи: npx vitest run resources/js/__tests__ (весь сьют зелёный) + npm run build (успешно) → отдельный коммит → отметить [x] с датой. Не закрывать задачу без зелёных прогонов.

## Фаза 0. Мёртвый код (1 сессия)
- [x] T0.1 Удалить: layouts/EmptyLayout.vue (ветка в LayoutSwitcher недостижима — currentTheme ∈ {default,warm,landing}; удалить и саму ветку из map), пустые папки: api/, Pages/Themes/Landing/, components/shared/Editor/composables/. Проверка: grep ссылок на удалённое, vitest, build. — 2026-11-07

## Фаза 1. Одиночные потребители (1 сессия)
- [x] T1.1 Перенести layouts/DashboardLayout.vue → modules/UserDashboard/ui/layouts/DashboardLayout.vue. Обновить импорты (6 страниц UserDashboard + grep по проекту). Проверка: vitest, build, кабинет открывается визуально. — 2026-11-07
- [ ] T1.2 Перенести components/shared/AuthCard.vue → modules/UserManager/ui/Auth/AuthCard.vue. Обновить импорты (Login.vue, Register.vue). Проверка: vitest, build, страницы логина/регистрации визуально.

## Фаза 2. Editor-кластер → MaterialManager (3 сессии)
Цель: весь поддомен редактора живёт внутри модуля MaterialManager.
- [ ] T2.1 Hexagon (только .ts): domain/{ports,values}/**, application/usecases/**, infrastructure/{adapters,di}/** → modules/MaterialManager/{domain,application,infrastructure}/** (зеркалить текущие пути). Обновить импорты UI/тестов на новые пути. Проверка: vitest, build. UI и тесты пока не двигаем.
- [ ] T2.2 UI: components/shared/Editor/** → modules/MaterialManager/ui/editor/; components/shared/{FormSelectModal,GallerySelectModal}.vue → modules/MaterialManager/ui/editor/modals/; composables/useEditor{,Commands,Selection}.ts → modules/MaterialManager/ui/editor/composables/. Обновить импорты (Edit.vue, Create.vue, тесты). Проверка: vitest, build, визуально: редактор открывается, тулбар, вставка галереи/формы.
- [ ] T2.3 Тесты: __tests__/{application,composables,editor,infrastructure}/** → __tests__/modules/material-manager/editor/**. Обновить пути импортов. Проверка: vitest (весь сьют).

## Фаза 3. Публичный сайт → modules/Site (после фаз 0–2, по одной задаче за сессию)
- [ ] T3.0 Решение по структуре: зафиксировать здесь целевые имена Inertia-страниц (например Site/Home, Site/Category/Show...) и структуру modules/Site/ui/. Без кода — только план, согласовать с человеком.
- [ ] T3.1 Errors: Pages/Errors/{Forbidden,429,NotFound,ServerError}.vue → modules/Site/ui/errors/. Обновить: bootstrap/app.php (exception handlers), AdminMiddleware, app.ts resolver. Проверка: vitest, build, страницы 403/404/429/500 визуально.
- [ ] T3.2 Index/Category/Search → modules/Site/ui/pages/... Обновить PHP Inertia::render имена в контроллерах + resolver. Проверка: vitest, build, публичные страницы визуально.
- [ ] T3.3 landing/Index → modules/Site/ui/landing/. Проверка: build, лендинг визуально.

## После всех фаз
- [ ] T4.1 Финальная проверка: вне modules/ остались только app.ts, d.ts, layouts/{AdminLayout,LayoutSwitcher,Default,Warm,Landing}, components/Admin/**, components/shared/{Toast,ToastContainer,ConfirmModal,CookieConsent,GlobalLightbox,ShortcodeRenderer,MenuItem,Breadcrumbs}, composables/{useToast,useSidebarSections}, services/, themes/, css/, types/, __tests__ (только modules/). Аудит повторить (тот же промт), сверить.