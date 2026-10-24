<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->boolean('show_date')->default(true);
            $table->boolean('show_author')->default(true);
            $table->boolean('show_category')->default(true);
            $table->boolean('show_views')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['show_date', 'show_author', 'show_category', 'show_views']);
        });
    }
};
