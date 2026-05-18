<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auto_matchmaking_queues', function (Blueprint $table) {
            if (! Schema::hasColumn('auto_matchmaking_queues', 'radius_km')) {
                $table->unsignedSmallInteger('radius_km')->default(10)->after('duration_minutes');
            }

            if (! Schema::hasColumn('auto_matchmaking_queues', 'expired_at')) {
                $table->timestamp('expired_at')->nullable()->after('matched_at');
            }

            if (! Schema::hasColumn('auto_matchmaking_queues', 'last_checked_at')) {
                $table->timestamp('last_checked_at')->nullable()->after('expired_at');
            }
        });

        DB::statement("ALTER TABLE auto_matchmaking_queues MODIFY status ENUM('waiting','searching','matched','cancelled','expired') NOT NULL DEFAULT 'searching'");
    }

    public function down(): void
    {
        Schema::table('auto_matchmaking_queues', function (Blueprint $table) {
            $table->dropColumn(['radius_km', 'expired_at', 'last_checked_at']);
        });

        DB::statement("ALTER TABLE auto_matchmaking_queues MODIFY status ENUM('waiting','matched','cancelled','expired') NOT NULL DEFAULT 'waiting'");
    }
};
