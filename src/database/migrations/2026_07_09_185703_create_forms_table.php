<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('alias')->unique();
            $table->text('description')->nullable();
            $table->json('fields')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('is_dynamic')->default(false);
            $table->integer('submissions_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
