<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('password');
            }
            if (! Schema::hasColumn('users', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('whatsapp');
            }
        });

        Schema::table('teams', function (Blueprint $table) {
            if (! Schema::hasColumn('teams', 'description')) {
                $table->text('description')->nullable()->after('city');
            }
            if (! Schema::hasColumn('teams', 'contact_number')) {
                $table->string('contact_number')->nullable()->after('skill_level');
            }
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
        // note: dropColumn ignores missing columns silently on most drivers
    }
};
