<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            // Проверяем и добавляем только отсутствующие поля
            if (!Schema::hasColumn('materials', 'slug')) {
                $table->string('slug')->unique()->nullable()->after('title');
            }

            if (!Schema::hasColumn('materials', 'featured')) {
                $table->boolean('featured')->default(false)->after('published_at');
            }

            if (!Schema::hasColumn('materials', 'show_on_homepage')) {
                $table->boolean('show_on_homepage')->default(false)->after('featured');
            }

            if (!Schema::hasColumn('materials', 'show_date')) {
                $table->boolean('show_date')->default(true)->after('show_on_homepage');
            }

            if (!Schema::hasColumn('materials', 'show_author')) {
                $table->boolean('show_author')->default(true)->after('show_date');
            }

            if (!Schema::hasColumn('materials', 'show_category')) {
                $table->boolean('show_category')->default(true)->after('show_author');
            }

            if (!Schema::hasColumn('materials', 'show_views')) {
                $table->boolean('show_views')->default(true)->after('show_category');
            }

            if (!Schema::hasColumn('materials', 'use_global_settings')) {
                $table->boolean('use_global_settings')->default(true)->after('show_views');
            }

            if (!Schema::hasColumn('materials', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }

            if (!Schema::hasColumn('materials', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'featured',
                'show_on_homepage',
                'show_date',
                'show_author',
                'show_category',
                'show_views',
                'use_global_settings',
                'deleted_at',
            ]);
        });
    }
};
