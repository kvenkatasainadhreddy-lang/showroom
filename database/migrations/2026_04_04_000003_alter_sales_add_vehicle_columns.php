<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('sale_type', ['vehicle', 'parts'])->default('parts')->after('invoice_no');

            $table->foreignId('vehicle_stock_id')
                ->nullable()->after('sale_type')
                ->constrained('vehicle_stock')->onDelete('set null');

            $table->foreignId('salesman_id')
                ->nullable()->after('sold_by')
                ->constrained('users')->onDelete('set null');

            $table->decimal('cash_amount',    12, 2)->nullable()->default(0)->after('amount_paid');
            $table->decimal('advance_amount', 12, 2)->nullable()->default(0)->after('cash_amount');
            $table->string('finance_name')->nullable()->after('advance_amount');
            $table->decimal('finance_amount', 12, 2)->nullable()->default(0)->after('finance_name');
            $table->decimal('balance_amount', 12, 2)->nullable()->default(0)->after('finance_amount');

            $table->string('customer_name')->nullable()->after('customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['vehicle_stock_id']);
            $table->dropForeign(['salesman_id']);
            $table->dropColumn([
                'sale_type', 'vehicle_stock_id', 'salesman_id',
                'cash_amount', 'advance_amount', 'finance_name',
                'finance_amount', 'balance_amount', 'customer_name',
            ]);
        });
    }
};
