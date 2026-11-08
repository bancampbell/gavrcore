<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Уникальность slug материала переведена с глобальной на двухуровневую:
     * в рамках категории для привязанных материалов и глобальную среди
     * безкатегорийных (category_id IS NULL). Данных с дублями в БД нет,
     * поэтому миграция не нормализует существующие записи.
     */
    public function up(): void
    {
        // Глобальный unique на slug: на migrate:fresh его создаёт только
        // идемпотентная ветка 2026_07_08, на старых БД — обе. IF EXISTS
        // покрывает оба случая.
        DB::statement('ALTER TABLE materials DROP CONSTRAINT IF EXISTS materials_slug_unique');
        DB::statement('DROP INDEX IF EXISTS materials_slug_unique');
        // alias материала — зеркало slug (MaterialModel::fromDomain), его
        // глобальный unique заблокировал бы дубли slug в разных категориях.
        // Читающих alias материалов нет — снимаем только ограничение, колонку
        // оставляем.
        DB::statement('ALTER TABLE materials DROP CONSTRAINT IF EXISTS materials_alias_unique');
        DB::statement('DROP INDEX IF EXISTS materials_alias_unique');

        DB::statement('CREATE UNIQUE INDEX materials_category_slug_unique ON materials (category_id, slug) WHERE category_id IS NOT NULL');
        DB::statement('CREATE UNIQUE INDEX materials_slug_root_unique ON materials (slug) WHERE category_id IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS materials_category_slug_unique');
        DB::statement('DROP INDEX IF EXISTS materials_slug_root_unique');
        DB::statement('CREATE UNIQUE INDEX materials_slug_unique ON materials (slug)');
        DB::statement('CREATE UNIQUE INDEX materials_alias_unique ON materials (alias)');
    }
};
