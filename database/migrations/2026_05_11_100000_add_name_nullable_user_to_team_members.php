<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add name column if missing (idempotent).
        if (! Schema::hasColumn('team_members', 'name')) {
            Schema::table('team_members', function (Blueprint $table) {
                $table->string('name')->nullable()->after('team_id');
            });
        }

        // Back-fill name from linked user.
        DB::statement('
            UPDATE team_members tm
            JOIN users u ON u.id = tm.user_id
            SET tm.name = u.name
            WHERE tm.name IS NULL AND tm.user_id IS NOT NULL
        ');

        // The compound unique index (team_id, user_id) is currently the only index
        // starting with team_id, so MySQL uses it to support the team_id FK.
        // We must add a standalone team_id index first so the FK has a replacement,
        // then we can safely drop the unique composite index.
        $indexes = collect(DB::select('SHOW INDEX FROM team_members'))->pluck('Key_name')->unique();

        if (! $indexes->contains('team_members_team_id_index')) {
            DB::statement('ALTER TABLE team_members ADD INDEX team_members_team_id_index (team_id)');
        }

        if ($indexes->contains('team_members_team_id_user_id_unique')) {
            DB::statement('ALTER TABLE team_members DROP INDEX team_members_team_id_user_id_unique');
        }
    }

    public function down(): void
    {
        // Restore unique index (need to drop any dupes first to avoid constraint violation).
        DB::statement('DELETE tm1 FROM team_members tm1 INNER JOIN team_members tm2
            WHERE tm1.id > tm2.id AND tm1.team_id = tm2.team_id AND tm1.user_id = tm2.user_id');

        DB::statement('ALTER TABLE team_members ADD UNIQUE team_members_team_id_user_id_unique (team_id, user_id)');

        $indexes = collect(DB::select('SHOW INDEX FROM team_members'))->pluck('Key_name')->unique();
        if ($indexes->contains('team_members_team_id_index')) {
            DB::statement('ALTER TABLE team_members DROP INDEX team_members_team_id_index');
        }

        if (Schema::hasColumn('team_members', 'name')) {
            Schema::table('team_members', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
};
