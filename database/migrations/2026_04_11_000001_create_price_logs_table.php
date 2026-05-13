<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->enum('price_type', ['purchase', 'selling', 'both'])->default('selling');
            $table->decimal('old_purchase_price', 12, 2)->nullable();
            $table->decimal('new_purchase_price', 12, 2)->nullable();
            $table->decimal('old_selling_price',  12, 2)->nullable();
            $table->decimal('new_selling_price',  12, 2)->nullable();
            $table->decimal('old_margin_percent', 8, 2)->nullable();
            $table->decimal('new_margin_percent', 8, 2)->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_logs');
    }
};
