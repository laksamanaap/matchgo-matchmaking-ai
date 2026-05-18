<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE matches MODIFY status ENUM('pending','scheduled','confirmed','ongoing','completed','cancelled','expired') NOT NULL DEFAULT 'scheduled'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE matches MODIFY status ENUM('scheduled','ongoing','completed','cancelled') NOT NULL DEFAULT 'scheduled'");
    }
};
