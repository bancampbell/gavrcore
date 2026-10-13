<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Добавляем запись в таблицу settings
        DB::table('settings')->insert([
            'key' => 'homepage_type',
            'value' => 'material',
            'type' => 'string',
            'group' => 'general',
            'label' => 'Тип главной страницы',
            'order' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'homepage_type')->delete();
    }
};
