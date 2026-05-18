<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('whatsapp')->nullable()->after('password');
            $table->string('profile_photo')->nullable()->after('whatsapp');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->text('description')->nullable()->after('city');
            $table->string('contact_number')->nullable()->after('skill_level');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['description', 'contact_number']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['whatsapp', 'profile_photo']);
        });
    }
};
