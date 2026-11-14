<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Только для НЕ включённой 2FA totp_secret может быть заполнен
            // (pending-секрет между enable и confirm). Тип text: зашифрованный
            // cast кладёт сюда base64(IV+MAC), что длиннее 255.
            $table->text('totp_secret')->nullable()->after('last_login_ip');
            $table->boolean('totp_enabled')->default(false)->after('totp_secret');
            // Хеши резервных кодов (sha256), в открытом виде не хранятся.
            $table->json('totp_recovery_codes')->nullable()->after('totp_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['totp_secret', 'totp_enabled', 'totp_recovery_codes']);
        });
    }
};
