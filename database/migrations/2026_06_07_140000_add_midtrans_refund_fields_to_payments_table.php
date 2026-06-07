<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'midtrans_order_id')) {
                $table->string('midtrans_order_id')->nullable()->after('payment_status');
            }

            if (! Schema::hasColumn('payments', 'midtrans_transaction_id')) {
                $table->string('midtrans_transaction_id')->nullable()->after('midtrans_order_id');
            }

            if (! Schema::hasColumn('payments', 'refund_reference')) {
                $table->string('refund_reference')->nullable()->after('midtrans_transaction_id');
            }

            if (! Schema::hasColumn('payments', 'refund_note')) {
                $table->text('refund_note')->nullable()->after('refund_reference');
            }

            if (! Schema::hasColumn('payments', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('refund_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $columns = [
                'midtrans_order_id',
                'midtrans_transaction_id',
                'refund_reference',
                'refund_note',
                'refunded_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
