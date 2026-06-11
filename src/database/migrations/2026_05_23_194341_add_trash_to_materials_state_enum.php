<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // В PostgreSQL нужно удалить constraint и создать новый
        DB::statement('ALTER TABLE materials DROP CONSTRAINT materials_state_check');
        DB::statement("ALTER TABLE materials ADD CONSTRAINT materials_state_check CHECK (state IN ('published', 'draft', 'archived', 'trash'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE materials DROP CONSTRAINT materials_state_check');
        DB::statement("ALTER TABLE materials ADD CONSTRAINT materials_state_check CHECK (state IN ('published', 'draft', 'archived'))");
    }
};
