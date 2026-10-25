<?php

namespace Database\Seeders;

use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём типы меню (идемпотентно: повторный seed не дублирует)
        $mainMenu = MenuTypeModel::firstOrCreate(
            ['alias' => 'main-menu'],
            [
                'title' => 'Main Menu',
                'description' => 'Главное меню сайта',
                'ordering' => 1,
                'status' => true,
            ]
        );

        $loginMenu = MenuTypeModel::firstOrCreate(
            ['alias' => 'menu-login'],
            [
                'title' => 'Menu login',
                'description' => 'Меню для авторизованных пользователей',
                'ordering' => 2,
                'status' => true,
            ]
        );

        $nevidimia = MenuTypeModel::firstOrCreate(
            ['alias' => 'nevidimia'],
            [
                'title' => 'Nevidimia',
                'description' => 'Скрытое меню',
                'ordering' => 3,
                'status' => false,
            ]
        );

        // Создаём пункты для Main Menu
        MenuItemModel::firstOrCreate(
            ['menu_type_id' => $mainMenu->id, 'alias' => 'o-kompanii'],
            [
                'parent_id' => null,
                'title' => 'О компании',
                'link_type' => 'url',
                'link_value' => '/about',
                'target' => '_self',
                'ordering' => 1,
                'status' => true,
                'access' => 'all',
                'language' => 'all',
            ]
        );

        MenuItemModel::firstOrCreate(
            ['menu_type_id' => $mainMenu->id, 'alias' => 'rukovodstvo'],
            [
                'parent_id' => null,
                'title' => 'Руководство',
                'link_type' => 'url',
                'link_value' => '/management',
                'target' => '_self',
                'ordering' => 2,
                'status' => true,
                'access' => 'all',
                'language' => 'all',
            ]
        );

        MenuItemModel::firstOrCreate(
            ['menu_type_id' => $mainMenu->id, 'alias' => 'kontakty'],
            [
                'parent_id' => null,
                'title' => 'Контакты',
                'link_type' => 'url',
                'link_value' => '/contacts',
                'target' => '_self',
                'ordering' => 3,
                'status' => true,
                'access' => 'all',
                'language' => 'all',
            ]
        );
    }
}
