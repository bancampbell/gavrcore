<?php

return [
    /*
    |--------------------------------------------------------------------------
    | UserManager module
    |--------------------------------------------------------------------------
    |
    | Настройки модуля пользователей: пагинация по умолчанию для
    | административных списков и ключи системных прав доступа.
    |
    */

    'defaults' => [
        'per_page' => 20,
    ],

    'permission_keys' => [
        'admin_access' => 'admin.access',
        'users_manage' => 'users.manage',
        'groups_manage' => 'groups.manage',
        'permissions_manage' => 'permissions.manage',
    ],
];
