<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard: skip if column already exists (may have been added manually)
        if (!Schema::hasColumn('sales', 'exchange')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('exchange', 12, 2)->nullable()->default(0)->after('discount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales', 'exchange')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('exchange');
            });
        }
    }
};
