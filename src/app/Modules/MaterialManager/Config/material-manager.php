<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Material Manager Configuration
    |--------------------------------------------------------------------------
    */

    'per_page' => 10,

    'allowed_per_page' => [10, 25, 50, 100],

    'states' => [
        'published' => 'Опубликовано',
        'draft' => 'Не опубликовано',
        'archived' => 'Архив',
        'trash' => 'Корзина',
    ],

    'access_levels' => [
        'public' => 'Public',
        'registered' => 'Registered',
        'special' => 'Special',
    ],

    'templates' => [
        'default' => 'Default',
        'warm' => 'Warm',
        'landing' => 'Landing',
    ],

    'cache' => [
        'enabled' => env('MATERIAL_CACHE_ENABLED', true),
        'ttl' => env('MATERIAL_CACHE_TTL', 3600),
        'key_prefix' => 'material_',
    ],

    'seo' => [
        'meta_title_max_length' => 70,
        'meta_description_max_length' => 160,
    ],
];
