<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('field_id')
                ->nullable()
                ->after('venue_id')
                ->constrained('fields')
                ->nullOnDelete();
        });

        DB::statement('ALTER TABLE matches DROP FOREIGN KEY matches_venue_id_foreign');
        DB::statement('ALTER TABLE matches DROP FOREIGN KEY matches_team_b_id_foreign');
        DB::statement('ALTER TABLE matches MODIFY venue_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE matches MODIFY team_b_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE matches ADD CONSTRAINT matches_venue_id_foreign FOREIGN KEY (venue_id) REFERENCES venues(id)');
        DB::statement('ALTER TABLE matches ADD CONSTRAINT matches_team_b_id_foreign FOREIGN KEY (team_b_id) REFERENCES teams(id)');
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropForeign(['field_id']);
            $table->dropColumn('field_id');
        });

        DB::statement('ALTER TABLE matches DROP FOREIGN KEY matches_venue_id_foreign');
        DB::statement('ALTER TABLE matches DROP FOREIGN KEY matches_team_b_id_foreign');
        DB::statement('ALTER TABLE matches MODIFY venue_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE matches MODIFY team_b_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE matches ADD CONSTRAINT matches_venue_id_foreign FOREIGN KEY (venue_id) REFERENCES venues(id)');
        DB::statement('ALTER TABLE matches ADD CONSTRAINT matches_team_b_id_foreign FOREIGN KEY (team_b_id) REFERENCES teams(id)');
    }
};
