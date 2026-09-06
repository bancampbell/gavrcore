<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Сначала добавляем поле как nullable
            $table->string('username')->nullable()->after('name');
            $table->boolean('blocked')->default(false)->after('email');
            $table->boolean('activated')->default(true)->after('blocked');
            $table->timestamp('last_login_at')->nullable()->after('activated');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
        });

        // Заполняем username для существующих пользователей
        DB::table('users')->whereNull('username')->update([
            'username' => DB::raw("CONCAT('user_', id)"),
        ]);

        // Делаем поле not null...
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->change();
        });

        // ...и добавляем unique-индекс отдельным выражением:
        // change() НЕ применяет индексные модификаторы (unique), поэтому
        // unique()->change() давал бы колонку без индекса. Индекс критичен —
        // RegisterUserUseCase ловит гонку регистраций по дубликату username
        // через QueryException 1062/23505.
        Schema::table('users', function (Blueprint $table) {
            $table->unique('username', 'users_username_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_unique');
            $table->dropColumn(['username', 'blocked', 'activated', 'last_login_at', 'last_login_ip']);
        });
    }
};
