<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_costs', function (Blueprint $table) {
            if (! Schema::hasColumn('match_costs', 'dp_per_team')) {
                $table->integer('dp_per_team')->default(0)->after('cost_per_team');
            }

            if (! Schema::hasColumn('match_costs', 'handling_fee')) {
                $table->integer('handling_fee')->default(0)->after('dp_per_team');
            }
        });
    }

    public function down(): void
    {
        Schema::table('match_costs', function (Blueprint $table) {
            $table->dropColumn(['dp_per_team', 'handling_fee']);
        });
    }
};
