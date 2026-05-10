<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matchmaking_queues', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('captain_id')->constrained('users')->cascadeOnDelete();

            $table->enum('skill_level', ['casual', 'semi_pro', 'competitive']);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->unsignedTinyInteger('search_range_km')->default(5);
            $table->unsignedTinyInteger('level_tolerance')->default(1);

            $table->enum('status', ['waiting', 'matched', 'cancelled', 'expired'])->default('waiting');

            $table->timestamp('queued_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'queued_at']);
            $table->index(['team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matchmaking_queues');
    }
};
