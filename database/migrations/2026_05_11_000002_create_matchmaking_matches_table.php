<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matchmaking_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_a_id')->constrained('matchmaking_queues')->cascadeOnDelete();
            $table->foreignId('queue_b_id')->constrained('matchmaking_queues')->cascadeOnDelete();

            $table->decimal('compatibility_score', 5, 2);

            $table->boolean('accepted_by_a')->default(false);
            $table->boolean('accepted_by_b')->default(false);

            $table->enum('status', ['pending', 'accepted', 'rejected', 'timeout'])->default('pending');

            $table->foreignId('match_request_id')->nullable()
                ->constrained('match_requests')->nullOnDelete();

            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matchmaking_matches');
    }
};
