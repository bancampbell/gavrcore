<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_types', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Название типа меню');
            $table->string('alias')->unique()->comment('Алиас (mainmenu, loginmenu и т.д.)');
            $table->string('description')->nullable()->comment('Описание');
            $table->integer('ordering')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_types');
    }
};
