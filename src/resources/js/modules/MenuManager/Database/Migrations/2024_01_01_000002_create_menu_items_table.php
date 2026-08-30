<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_type_id')->constrained('menu_types')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade');
            $table->string('title');
            $table->string('alias')->nullable();
            $table->string('link_type')->default('url');
            $table->string('link_value')->nullable();
            $table->string('target')->default('_self');
            $table->integer('ordering')->default(0);
            $table->boolean('status')->default(true);
            $table->string('access')->default('all');
            $table->string('language')->default('all');
            $table->timestamps();
            $table->unique(['alias', 'menu_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};