<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fields')) {
            Schema::create('fields', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('image')->nullable();
                $table->json('images')->nullable();
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->integer('price_per_hour')->default(0);
                $table->string('contact_phone')->nullable();
                $table->time('open_time')->nullable();
                $table->time('close_time')->nullable();
                $table->boolean('is_available')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('matches', 'field_id')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->foreignId('field_id')->nullable()->after('venue_id')->constrained('fields')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('bookings', 'field_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreignId('field_id')->nullable()->after('id')->constrained('fields')->nullOnDelete();
            });
        }

        // Buang kolom venue_id sisa dari konsolidasi sebelumnya (kembali ke field_id).
        if (Schema::hasColumn('bookings', 'venue_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['venue_id']);
                $table->dropColumn('venue_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('matches', 'field_id')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->dropForeign(['field_id']);
                $table->dropColumn('field_id');
            });
        }

        if (Schema::hasColumn('bookings', 'field_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['field_id']);
                $table->dropColumn('field_id');
            });
        }

        Schema::dropIfExists('fields');
    }
};
