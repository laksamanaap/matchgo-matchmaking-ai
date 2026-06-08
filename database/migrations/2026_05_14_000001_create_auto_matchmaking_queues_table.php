<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('auto_matchmaking_queues')) {
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

                $table->index(['status', 'skill_level', 'match_date', 'start_time', 'duration_minutes'], 'auto_queue_lookup_idx');
            });

            DB::statement("ALTER TABLE auto_matchmaking_queues MODIFY status ENUM('waiting','searching','matched','cancelled','expired') NOT NULL DEFAULT 'searching'");

            return;
        }

        Schema::create('auto_matchmaking_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('match_id')->nullable()->constrained('matches')->nullOnDelete();
            $table->date('match_date')->nullable();
            $table->time('start_time')->nullable();
            $table->integer('duration_minutes')->default(90);
            $table->unsignedSmallInteger('radius_km')->default(10);
            $table->string('skill_level');
            $table->enum('status', ['searching', 'matched', 'cancelled', 'expired'])->default('searching');
            $table->timestamp('matched_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'skill_level', 'match_date', 'start_time', 'duration_minutes'], 'auto_queue_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_matchmaking_queues');
    }
};
