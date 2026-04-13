<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('player', 'admin', 'auditor', 'super_admin') NOT NULL DEFAULT 'player'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('player', 'admin', 'auditor') NOT NULL DEFAULT 'player'");
    }
};
