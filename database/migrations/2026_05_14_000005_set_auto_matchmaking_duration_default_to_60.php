<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE auto_matchmaking_queues MODIFY duration_minutes INT NOT NULL DEFAULT 60');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE auto_matchmaking_queues MODIFY duration_minutes INT NOT NULL DEFAULT 90');
    }
};
