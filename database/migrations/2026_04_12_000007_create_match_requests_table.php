<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('opponent_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled'])->default('pending');
            $table->date('preferred_date');
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_requests');
    }
};
