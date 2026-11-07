# TECHDEBT — зачистка после деплоя (2026-11)

## Бэкенд
1. **MediaManager** — аудит-лог мёртв: 5 доменных событий нигде не диспатчатся, media_audit_logs всегда пуст. Решить: диспатчить события или вычистить инфраструктуру (миграция+listener+тесты).
2. **MenuManager** — (а) race: два конкурентных create пунктов в пустой scope дают дублирование ordering=0 (как GalleryManager раньше — там закрыли блокировкой); (б) UpdateMenuTypeUseCase без retry на unique-нарушение (в FormBuilder такой же фикс был сделан — унифицировать).
3. **SettingManager / ThemeManager** — auth()-&gt;user() целиком в Inertia props (password hash в исходнике страницы). Передавать only(id,name,email) как в соседних контроллерах.
4. **Единая обработка гонки lazy-create** по settings.key: SettingService::set / ThemeService::setCurrentTheme / initializeDefaultSettings — паттерн find→create без retry (в SettingManager частично закрыт, унифицировать).
5. **Унифицировать детект unique-violation** по эталону UserManager (errorInfo[0]/getCode(), не errorInfo[1]). Проверить FormBuilder (str_contains по тексту ошибки), MaterialManager, MenuManager.
6. **GalleryManager** — GetGalleryListUseCase фильтрует в памяти (тянет всю таблицу). Фильтры и пагинация — в SQL.
7. **Все модули** — голые RuntimeException без доменной типизации (контроллер не отличит 404 от 500).
8. **UserManager** — AdminUserCommandController::store и AdminGroupCommandController::store всегда возвращают redirect()->route() (HTML), хотя их же update и store уровней доступа честно различают expectsJson. Axios во фронте молча следует за 302 и получает 200+HTML: создание пользователя/группы работает «на глазок», тело ответа непригодно для проверки результата (AdminUserCommandController.php:44, AdminGroupCommandController.php:24).

## Фронтенд
8. **AdminDashboard** — getSubjectName() мёртв: subject_type не существует в данных бэкенда. Удалить или переписать по реальной структуре activities.
9. **CategoryManager** — alias правило UpdateCategoryRequest: добавить nullable, ЕСЛИ появятся записи с NULL alias (ConvertEmptyStringsToNull ловит '' → null → 422, сейчас записей 0).
10. **GalleryManager** — клик по названию в desktop-таблице дублирует toggleSelect (выделение строки прыгает при навигации).
11. **SeoManager** — meta.schema никем не заполняется (проверено: ни один SeoProvider) → v-html-ветка в Show.vue мертва. Удалить ветку или заполнить провайдеров.
12. **MaterialManager** — (а) 'state' нет в sort-whitelist бэкенда → сортировка по колонке «Статус» молча не работает; (б) Trash.vue передаёт MouseEvent в count → текст модалки «[object MouseEvent]».
13. **MenuManager** — Index.vue:128 router.visit(..., {props}) — опции props в Inertia нет, молча игнорируется. Убрать мёртвую опцию.
14. **FormBuilder** — notification.type='info' не в union Toast/ConfirmModal (рендерится стилем ошибки); клик по &lt;Link&gt; в строке таблицы триггерит toggleSelect.
15. **MediaManager** — selectedFileForPicker экспонирован, но никем не вызывается; selectedFiles не очищается после неудачной загрузки.
16. **По всем модулям** — лишний `Authorization: Bearer &lt;localStorage token&gt;` на session-эндпоинтах (безвредно, но шумно); мёртвые методы в api-слоях и composables (submitForm/form/openEditModal, getById/updateOrdering/getAll и т.п.).
17. **SettingManager** — saveAndClose ведёт на /admin/dashboard?message=..., но дашборд query-параметр message не читает → подтверждение «Настройки сохранены» после переадресации молча не показывается (Index.vue:493). Передать флеш через Inertia-shared prop или убрать параметр.
18. **ThemeManager** — (а) захардкоженные URL вместо route(): '/admin/themes' (Index.vue:190) и '/admin/dashboard' (Index.vue:237) — при смене prefix/name роутов фронт молча поедет; (б) после успешного применения темы — полный window.location.reload() (Index.vue:205) вместо обновления через Inertia-навигацию.
19. **UserDashboard** — (а) TicketsCreate.vue:32 шорткод `[form id="5"]` захардкожен: удаление/деактивация формы id=5 ломает страницу «Новая заявка» («Форма не найдена») — вынести id в пропс/настройку; (б) Profile.vue:72 и Settings.vue:89 onError получает объект ошибок валидации Inertia, err.message всегда undefined → текст серверных ошибок (занятый email, неверный текущий пароль) не показывается, только generic-текст; (в) TicketsShow.vue:49 formTitle объявлен String, бэкенд может отдать null ($form?->title) — dev-warning, шаблон null уже обрабатывает; (г) во всех страницах модуля хардкоженные URL вместо route() (тот же квирк, что ThemeManager п.18).
20. **UserManager** — Users/Create.vue:285 и Users/Edit.vue:231,253 при 422 показывают error.response.data.message (англ. «The given data was invalid.»), полевые errors.* (email/username/blocked/groups) не читаются — в отличие от Groups/Edit.vue:290; самоблокировка/самодеактивация админом показывается англ. generic-текстом.
21. **UserManager** — фильтры в пропсах строками: $request->only(['search','blocked'])/['search','status'] отдаёт 'true'/'false' строками → после F5 select статуса (boolean-опции) не сматчивается и активный фильтр визуально сброшен, хотя список отфильтрован (useUsers.ts:8, useGroups.ts:8).
22. **UserManager** — AccessLevels: пагинация не пересчитывается после router.reload({only:['accessLevels']}) в confirmDelete/confirmToggleStatus — «Показано X–Y из Z» устаревает до смены фильтра/страницы (useAccessLevels.ts:137-153,223).
23. **UserManager** — Groups/Create.vue:165 updateAlias затирает вручную введённый алиас при каждом вводе названия (флага aliasEdited, как в Edit.vue:190, в Create нет).