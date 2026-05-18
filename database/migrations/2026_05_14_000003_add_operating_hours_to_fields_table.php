<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fields', function (Blueprint $table) {
            if (! Schema::hasColumn('fields', 'open_time')) {
                $table->time('open_time')->nullable()->after('contact_phone');
            }

            if (! Schema::hasColumn('fields', 'close_time')) {
                $table->time('close_time')->nullable()->after('open_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fields', function (Blueprint $table) {
            $table->dropColumn(['open_time', 'close_time']);
        });
    }
};
