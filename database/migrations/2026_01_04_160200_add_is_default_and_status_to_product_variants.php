<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                if (!Schema::hasColumn('product_variants', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('stock');
                }
                if (!Schema::hasColumn('product_variants', 'status')) {
                    $table->string('status', 50)->nullable()->after('is_default');
                }
                if (!Schema::hasColumn('product_variants', 'sku')) {
                    $table->string('sku')->nullable()->unique()->after('size');
                }
            });
        }
    }

    public function down(): void
    {
        // keep columns
    }
};
