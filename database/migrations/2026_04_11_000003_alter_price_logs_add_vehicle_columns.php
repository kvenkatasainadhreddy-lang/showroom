<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make product_id nullable via raw SQL (avoids doctrine/dbal dependency)
        DB::statement('ALTER TABLE price_logs MODIFY product_id BIGINT UNSIGNED NULL');

        Schema::table('price_logs', function (Blueprint $table) {
            $table->enum('entity_type', ['product', 'vehicle_variant', 'vehicle_stock'])
                  ->default('product')->after('id');

            $table->string('price_field', 50)->nullable()->after('entity_type');

            $table->unsignedBigInteger('vehicle_variant_id')->nullable()->after('product_id');
            $table->unsignedBigInteger('vehicle_stock_id')->nullable()->after('vehicle_variant_id');

            $table->foreign('vehicle_variant_id')
                  ->references('id')->on('vehicle_variants')->onDelete('cascade');
            $table->foreign('vehicle_stock_id')
                  ->references('id')->on('vehicle_stock')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('price_logs', function (Blueprint $table) {
            $table->dropForeign(['vehicle_variant_id']);
            $table->dropForeign(['vehicle_stock_id']);
            $table->dropColumn(['entity_type', 'price_field', 'vehicle_variant_id', 'vehicle_stock_id']);
        });
        DB::statement('ALTER TABLE price_logs MODIFY product_id BIGINT UNSIGNED NOT NULL');
    }
};
