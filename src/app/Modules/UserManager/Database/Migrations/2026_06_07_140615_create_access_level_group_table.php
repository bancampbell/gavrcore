<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_level_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_level_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Системные группы, к которым привязываются уровни доступа.
        // Создаём здесь, а не в сидере: сидер выполняется после миграций,
        // и на момент этой миграции групп managers/administrators ещё нет.
        $now = now();

        DB::table('groups')->insertOrIgnore([
            ['name' => 'Administrators', 'alias' => 'administrators', 'description' => 'Full system access', 'status' => true, 'ordering' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Managers', 'alias' => 'managers', 'description' => 'Content management access', 'status' => true, 'ordering' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Registered', 'alias' => 'registered', 'description' => 'Registered users', 'status' => true, 'ordering' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Public', 'alias' => 'public', 'description' => 'All site visitors', 'status' => true, 'ordering' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Guest', 'alias' => 'guest', 'description' => 'Guest users', 'status' => true, 'ordering' => 5, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Назначаем группы к уровням доступа:
        // Public       — public, guest, registered, managers, administrators
        // Guest        — guest
        // Registered   — registered, managers, administrators
        // Special      — managers, administrators
        // Super Users  — administrators
        $levels = DB::table('access_levels')
            ->whereIn('alias', ['public', 'guest', 'registered', 'special', 'super-users'])
            ->pluck('id', 'alias');

        $groups = DB::table('groups')
            ->whereIn('alias', ['public', 'guest', 'registered', 'managers', 'administrators'])
            ->pluck('id', 'alias');

        $map = [
            'public' => ['public', 'guest', 'registered', 'managers', 'administrators'],
            'guest' => ['guest'],
            'registered' => ['registered', 'managers', 'administrators'],
            'special' => ['managers', 'administrators'],
            'super-users' => ['administrators'],
        ];

        foreach ($map as $levelAlias => $groupAliases) {
            $levelId = $levels->get($levelAlias);

            if (! $levelId) {
                continue;
            }

            foreach ($groupAliases as $groupAlias) {
                $groupId = $groups->get($groupAlias);

                if (! $groupId) {
                    continue;
                }

                DB::table('access_level_group')->insert([
                    'access_level_id' => $levelId,
                    'group_id' => $groupId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('access_level_group');
    }
};
