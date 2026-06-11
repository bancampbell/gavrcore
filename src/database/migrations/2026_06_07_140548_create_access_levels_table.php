<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_levels', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Название уровня доступа');
            $table->string('alias')->unique()->comment('Алиас (public, guest, registered и т.д.)');
            $table->text('description')->nullable();
            $table->integer('ordering')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Создаём стандартные уровни доступа
        DB::table('access_levels')->insert([
            ['title' => 'Public', 'alias' => 'public', 'ordering' => 1, 'status' => true, 'created_at' => now()],
            ['title' => 'Guest', 'alias' => 'guest', 'ordering' => 2, 'status' => true, 'created_at' => now()],
            ['title' => 'Registered', 'alias' => 'registered', 'ordering' => 3, 'status' => true, 'created_at' => now()],
            ['title' => 'Special', 'alias' => 'special', 'ordering' => 4, 'status' => true, 'created_at' => now()],
            ['title' => 'Super Users', 'alias' => 'super-users', 'ordering' => 5, 'status' => true, 'created_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('access_levels');
    }
};
