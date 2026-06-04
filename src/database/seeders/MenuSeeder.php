<?php

namespace Database\Seeders;

use App\Models\MenuType;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём типы меню
        $mainMenu = MenuType::create([
            'title' => 'Main Menu',
            'alias' => 'mainmenu',
            'description' => 'Главное меню сайта',
            'ordering' => 1,
            'status' => true,
        ]);

        $loginMenu = MenuType::create([
            'title' => 'Menu login',
            'alias' => 'menulogin',
            'description' => 'Меню для авторизованных пользователей',
            'ordering' => 2,
            'status' => true,
        ]);

        $nevidimia = MenuType::create([
            'title' => 'Nevidimia',
            'alias' => 'nevidimia',
            'description' => 'Скрытое меню',
            'ordering' => 3,
            'status' => false,
        ]);

        // Создаём пункты для Main Menu
        MenuItem::create([
            'menu_type_id' => $mainMenu->id,
            'parent_id' => null,
            'title' => 'О компании',
            'alias' => 'o-kompanii',
            'link_type' => 'url',
            'link_value' => '/about',
            'target' => '_self',
            'ordering' => 1,
            'status' => true,
            'access' => 'all',
            'language' => 'all',
        ]);

        MenuItem::create([
            'menu_type_id' => $mainMenu->id,
            'parent_id' => null,
            'title' => 'Руководство',
            'alias' => 'rukovodstvo',
            'link_type' => 'url',
            'link_value' => '/management',
            'target' => '_self',
            'ordering' => 2,
            'status' => true,
            'access' => 'all',
            'language' => 'all',
        ]);

        MenuItem::create([
            'menu_type_id' => $mainMenu->id,
            'parent_id' => null,
            'title' => 'Контакты',
            'alias' => 'kontakty',
            'link_type' => 'url',
            'link_value' => '/contacts',
            'target' => '_self',
            'ordering' => 3,
            'status' => true,
            'access' => 'all',
            'language' => 'all',
        ]);
    }
}
