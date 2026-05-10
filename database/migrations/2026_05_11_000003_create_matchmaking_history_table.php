<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matchmaking_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('matched_team_id')->nullable()
                ->constrained('teams')->nullOnDelete();

            $table->decimal('compatibility_score', 5, 2)->nullable();
            $table->unsignedInteger('queue_duration_seconds')->default(0);

            $table->enum('result', ['accepted', 'rejected', 'timeout', 'cancelled']);

            $table->timestamps();

            $table->index(['team_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matchmaking_history');
    }
};
