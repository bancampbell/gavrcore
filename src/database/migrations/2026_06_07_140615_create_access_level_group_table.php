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

        // Назначаем группы к уровням доступа
        // Public - доступ у всех (Public, Guest, Registered, Manager, Admin)
        // Guest - только Guest
        // Registered - Registered, Manager, Admin
        // Special - Manager, Admin
        // Super Users - Admin

        $publicLevel = DB::table('access_levels')->where('alias', 'public')->first();
        $guestLevel = DB::table('access_levels')->where('alias', 'guest')->first();
        $registeredLevel = DB::table('access_levels')->where('alias', 'registered')->first();
        $specialLevel = DB::table('access_levels')->where('alias', 'special')->first();
        $superUsersLevel = DB::table('access_levels')->where('alias', 'super-users')->first();

        $publicGroup = DB::table('groups')->where('alias', 'public')->first();
        $guestGroup = DB::table('groups')->where('alias', 'guest')->first();
        $registeredGroup = DB::table('groups')->where('alias', 'registered')->first();
        $managerGroup = DB::table('groups')->where('alias', 'managers')->first();
        $adminGroup = DB::table('groups')->where('alias', 'administrators')->first();

        if ($publicLevel && $publicGroup) {
            DB::table('access_level_group')->insert(['access_level_id' => $publicLevel->id, 'group_id' => $publicGroup->id]);
        }
        if ($guestLevel && $guestGroup) {
            DB::table('access_level_group')->insert(['access_level_id' => $guestLevel->id, 'group_id' => $guestGroup->id]);
        }
        if ($registeredLevel && $registeredGroup) {
            DB::table('access_level_group')->insert(['access_level_id' => $registeredLevel->id, 'group_id' => $registeredGroup->id]);
        }
        if ($registeredLevel && $managerGroup) {
            DB::table('access_level_group')->insert(['access_level_id' => $registeredLevel->id, 'group_id' => $managerGroup->id]);
        }
        if ($registeredLevel && $adminGroup) {
            DB::table('access_level_group')->insert(['access_level_id' => $registeredLevel->id, 'group_id' => $adminGroup->id]);
        }
        if ($specialLevel && $managerGroup) {
            DB::table('access_level_group')->insert(['access_level_id' => $specialLevel->id, 'group_id' => $managerGroup->id]);
        }
        if ($specialLevel && $adminGroup) {
            DB::table('access_level_group')->insert(['access_level_id' => $specialLevel->id, 'group_id' => $adminGroup->id]);
        }
        if ($superUsersLevel && $adminGroup) {
            DB::table('access_level_group')->insert(['access_level_id' => $superUsersLevel->id, 'group_id' => $adminGroup->id]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('access_level_group');
    }
};
